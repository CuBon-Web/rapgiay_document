<?php



namespace App\Http\Controllers\Client;



use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Customer;

use Auth, Validator, DB, Hash, Mail;

use App\Mail\CustomerPasswordOtpMail;

use App\models\Bill\Bill;
use App\models\Bill\BillDetail;

use App\Support\OrderDocumentDelivery;



class AuthController extends Controller

{

    private const OTP_EXPIRE_MINUTES = 10;

    private const OTP_RESEND_SECONDS = 60;



    public function login()

    {

        return view('auth.login', [

            'intended' => session('auth_intended', request('redirect')),

        ]);

    }



    public function postLogin(Request $request)

    {

        $credentials = $request->only('email', 'password');

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {

            $intended = $request->input('intended', session('auth_intended'));

            $request->session()->forget('auth_intended');

            if ($intended && $this->isSafeRedirectUrl($intended)) {

                return redirect($intended)->with('success', 'Đăng nhập thành công');

            }



            return redirect('/')->with('success', 'Đăng nhập thành công');

        }



        return back()

            ->with('error', 'Đăng nhập thất bại, vui lòng kiểm tra email và mật khẩu')

            ->withInput($request->only('email', 'intended'));

    }



    private function isSafeRedirectUrl($url)

    {

        if (!is_string($url) || $url === '') {

            return false;

        }



        $appRoot = rtrim(url('/'), '/');



        if ($url === $appRoot) {

            return true;

        }



        return strncmp($url, $appRoot . '/', strlen($appRoot) + 1) === 0;

    }



    public function register()

    {

        return view('auth.register');

    }



    public function postRegister(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'email' => 'required|email|unique:customer',

            'password' => 'required|min:8|confirmed',

            'name' => 'required',

            'phone' => 'required',

        ]);



        if ($validator->fails()) {

            return back()

                ->withErrors($validator)

                ->withInput();

        }



        $data = new Customer();

        $data->email = $request->email;

        $data->password = bcrypt($request->password);

        $data->name = $request->name;

        $data->phone = $request->phone;

        $data->status = 0;

        $data->save();



        return redirect()->route('login')

            ->with('success', 'Đăng ký tài khoản thành công. Vui lòng đăng nhập để tiếp tục.');

    }



    public function showForgotPassword()

    {

        return view('auth.forgot-password');

    }



    public function sendForgotPasswordOtp(Request $request)

    {

        $request->validate([

            'email' => 'required|email',

        ]);



        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {

            return back()

                ->with('error', 'Email không tồn tại trong hệ thống.')

                ->withInput();

        }



        $existing = DB::table('customer_password_otps')->where('email', $customer->email)->first();

        if ($existing && \Carbon\Carbon::parse($existing->updated_at)->diffInSeconds(now()) < self::OTP_RESEND_SECONDS) {

            return back()

                ->with('error', 'Vui lòng đợi 1 phút trước khi gửi lại mã OTP.')

                ->withInput();

        }



        $otp = $this->generateOtp();

        DB::table('customer_password_otps')->updateOrInsert(

            ['email' => $customer->email],

            [

                'otp_hash' => Hash::make($otp),

                'expires_at' => now()->addMinutes(self::OTP_EXPIRE_MINUTES),

                'updated_at' => now(),

                'created_at' => $existing ? $existing->created_at : now(),

            ]

        );



        try {

            Mail::to($customer->email)->send(new CustomerPasswordOtpMail($otp, $customer->name));

        } catch (\Exception $e) {

            return back()

                ->with('error', 'Không thể gửi email. Vui lòng thử lại sau.')

                ->withInput();

        }



        session(['password_reset_email' => $customer->email]);



        return redirect()->route('password.reset.form')

            ->with('success', 'Mã OTP gồm 6 chữ số đã được gửi tới email của bạn. Vui lòng kiểm tra hộp thư và sao chép mã để nhập bên dưới.');

    }



    public function showResetPasswordForm()

    {

        $email = session('password_reset_email', old('email'));

        if (!$email) {

            return redirect()->route('password.forgot')

                ->with('error', 'Vui lòng nhập email để nhận mã OTP.');

        }



        return view('auth.reset-password-otp', ['email' => $email]);

    }



    public function resetPasswordWithOtp(Request $request)

    {

        $request->validate([

            'email' => 'required|email',

            'otp' => 'required|digits:6',

            'password' => 'required|min:8|confirmed',

        ]);



        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {

            return back()->with('error', 'Email không hợp lệ.')->withInput();

        }



        $record = DB::table('customer_password_otps')->where('email', $request->email)->first();

        if (!$record || now()->gt($record->expires_at)) {

            return back()

                ->with('error', 'Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.')

                ->withInput($request->except('password', 'password_confirmation'));

        }



        if (!Hash::check($request->otp, $record->otp_hash)) {

            return back()

                ->with('error', 'Mã OTP không đúng. Vui lòng kiểm tra lại.')

                ->withInput($request->except('password', 'password_confirmation'));

        }



        $customer->password = bcrypt($request->password);

        $customer->save();



        DB::table('customer_password_otps')->where('email', $request->email)->delete();

        session()->forget('password_reset_email');



        return redirect()->route('login')

            ->with('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập với mật khẩu mới.');

    }



    private function generateOtp()

    {

        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    }



    public function logout()

    {

        Auth::guard('customer')->logout();

        return redirect('/');

    }



    public function accoungOrder()
    {
        $profile = Auth::guard('customer')->user();
        $bills = Bill::where('code_customer', $profile->id)
            ->orderByDesc('created_at')
            ->get();

        $downloadCounts = [];
        foreach ($bills as $bill) {
            if ((int) $bill->statu === 1) {
                $downloadCounts[$bill->code_bill] = count(OrderDocumentDelivery::itemsForBill($bill));
            }
        }

        return view('auth.account-order', [
            'bill' => $bills,
            'profile' => $profile,
            'downloadCounts' => $downloadCounts,
        ]);
    }

    public function accoungOrderDetail($billid)
    {
        $profile = Auth::guard('customer')->user();
        $bill = Bill::where('code_bill', $billid)
            ->where('code_customer', $profile->id)
            ->firstOrFail();
        $downloads = OrderDocumentDelivery::itemsForBill($bill);

        return view('auth.account-order-detail', [
            'bill' => $bill,
            'billdetail' => BillDetail::where('code_bill', $billid)->get(),
            'downloads' => $downloads,
            'downloadsByProduct' => collect($downloads)->keyBy('product_id'),
            'profile' => $profile,
        ]);
    }

    public function showChangePassword()
    {
        $profile = Auth::guard('customer')->user();

        return view('auth.change-password', [
            'profile' => $profile,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $profile = Auth::guard('customer')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.password')
                ->withErrors($validator)
                ->withInput($request->except('current_password', 'password', 'password_confirmation'));
        }

        if (!Hash::check($request->current_password, $profile->password)) {
            return redirect()->route('account.password')
                ->with('error', 'Mật khẩu hiện tại không đúng.')
                ->withInput($request->except('current_password', 'password', 'password_confirmation'));
        }

        if (Hash::check($request->password, $profile->password)) {
            return redirect()->route('account.password')
                ->with('error', 'Mật khẩu mới phải khác mật khẩu hiện tại.');
        }

        $customer = Customer::find($profile->id);
        $customer->password = bcrypt($request->password);
        $customer->save();

        return redirect()->route('account.password')
            ->with('success', 'Đổi mật khẩu thành công.');
    }

}

