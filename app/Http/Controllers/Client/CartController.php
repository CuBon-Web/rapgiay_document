<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\models\product\Product;
use Cart,Auth,Redirect,DB;
use App\models\Bill\BillDetail;
use App\models\Bill\Bill;
use Mail;
use App\Mail\DemoMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\models\website\Setting;
use App\Support\GoogleDriveLink;
use App\Support\OrderDocumentDelivery;
use App\Support\DriveFolderZipBuilder;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private function getNotificationEmails()
    {
        $setting = Setting::first(['email']);
        if (!$setting || empty($setting->email)) {
            return [];
        }

        return collect(explode(',', (string)$setting->email))
            ->map(function ($email) {
                return trim($email);
            })
            ->filter(function ($email) {
                return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL);
            })
            ->values()
            ->all();
    }

    private function sendBillNotificationMail(Bill $bill, array $cart)
    {
        $emails = $this->getNotificationEmails();
        if (empty($emails)) {
            Log::warning('Order notification skipped: settings.email is empty or invalid', [
                'code_bill' => $bill->code_bill,
            ]);
            return;
        }

        $data = ['cus' => $bill, 'bill' => $cart];
        Mail::to($emails)->send(new DemoMail($data));
    }

    private function getCartLinePrice(array $item)
    {
        if ((int)($item['status_variant'] ?? 0) === 1) {
            return (int)($item['price'] ?? 0);
        }
        if ((int)($item['discount'] ?? 0) > 0) {
            return (int)($item['discount'] ?? 0);
        }
        return (int)($item['price'] ?? 0);
    }

    private function reduceProductQtyByBill(Bill $bill)
    {
        $billDetails = BillDetail::where('code_bill', $bill->code_bill)->get();
        foreach ($billDetails as $detail) {
            $product = Product::find($detail->code_product);
            if (!$product) continue;
            if ($product->qty > $detail->qty) {
                $product->qty = $product->qty - $detail->qty;
            } else {
                $product->qty = 0;
            }
            $product->save();
        }
    }

    private function buildPayosSignature(array $data, $checksumKey)
    {
        ksort($data);
        $pairs = [];
        foreach ($data as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }
        return hash_hmac('sha256', implode('&', $pairs), $checksumKey);
    }

    private function createPayosPaymentLink(Bill $bill, array $cart, Request $request)
    {
        $clientId = env('PAYOS_CLIENT_ID');
        $apiKey = env('PAYOS_API_KEY');
        $checksumKey = env('PAYOS_CHECKSUM_KEY');
        if (!$clientId || !$apiKey || !$checksumKey) {
            throw new \Exception('Thiếu cấu hình PAYOS_CLIENT_ID/PAYOS_API_KEY/PAYOS_CHECKSUM_KEY');
        }

        $orderCode = (int)$bill->code_bill;
        $description = 'DH' . $orderCode;
        $returnUrl = route('payos.return');
        $cancelUrl = route('payos.cancel');

        $items = [];
        foreach ($cart as $item) {
            $items[] = [
                'name' => mb_substr($item['name'], 0, 25),
                'quantity' => (int)($item['quantity'] ?? 1),
                'price' => $this->getCartLinePrice($item),
            ];
        }

        $signatureData = [
            'amount' => (int)$bill->total_money,
            'cancelUrl' => $cancelUrl,
            'description' => $description,
            'orderCode' => $orderCode,
            'returnUrl' => $returnUrl,
        ];

        $payload = [
            'orderCode' => $orderCode,
            'amount' => (int)$bill->total_money,
            'description' => $description,
            'items' => $items,
            'returnUrl' => $returnUrl,
            'cancelUrl' => $cancelUrl,
            'buyerName' => $bill->cus_name,
            'buyerEmail' => $bill->cus_email,
            'buyerPhone' => $bill->cus_phone,
            'buyerAddress' => $bill->cus_address,
            'signature' => $this->buildPayosSignature($signatureData, $checksumKey),
        ];

        $response = Http::withHeaders([
            'x-client-id' => $clientId,
            'x-api-key' => $apiKey,
        ])->post('https://api-merchant.payos.vn/v2/payment-requests', $payload);

        if (!$response->ok()) {
            Log::error('PayOS create payment link failed', ['response' => $response->body()]);
            throw new \Exception('Không tạo được link thanh toán PayOS');
        }

        $json = $response->json();
        if (!isset($json['data']['checkoutUrl'])) {
            Log::error('PayOS checkoutUrl missing', ['response' => $json]);
            throw new \Exception('PayOS không trả về checkoutUrl');
        }
        return $json['data']['checkoutUrl'];
    }

    private function markBillPaidByOrderCode($orderCode)
    {
        $bill = Bill::where('code_bill', $orderCode)->first();
        if (!$bill) return null;
        if ((int)$bill->statu !== 1) {
            $bill->statu = 1;
            $bill->save();
            $this->reduceProductQtyByBill($bill);
            $billDetails = BillDetail::where('code_bill', $bill->code_bill)->get()->toArray();
            $this->sendBillNotificationMail($bill, $billDetails);
        }
        return $bill;
    }

    private function buildOrderDownloadItems(Bill $bill)
    {
        return OrderDocumentDelivery::itemsForBill($bill);
    }

    private function renderOrderSuccess(Bill $bill)
    {
        $downloads = $this->buildOrderDownloadItems($bill);
        session([
            'last_order_downloads' => $downloads,
            'last_order_code' => $bill->code_bill,
        ]);

        return view('cart.orderSuccess', [
            'downloads' => $downloads,
            'billCode' => $bill->code_bill,
        ]);
    }

    public function downloadPaidDocument(Request $request, $billCode, $productId)
    {
        $profile = Auth::guard('customer')->user();
        if (!$profile) {
            abort(403);
        }

        $bill = Bill::where('code_bill', $billCode)
            ->where('code_customer', $profile->id)
            ->where('statu', 1)
            ->firstOrFail();

        $ownsLine = BillDetail::where('code_bill', $bill->code_bill)
            ->where('code_product', $productId)
            ->exists();
        if (!$ownsLine) {
            abort(404);
        }

        $product = Product::findOrFail($productId);
        $driveUrl = trim((string) ($product->origin ?? ''));
        $detail = BillDetail::where('code_bill', $bill->code_bill)
            ->where('code_product', $productId)
            ->first();
        $productName = ($detail && $detail->name) ? $detail->name : $product->name;

        $folderId = GoogleDriveLink::extractFolderId($driveUrl);
        if ($folderId && GoogleDriveLink::extractFileId($driveUrl) === null) {
            return $this->downloadPaidFolderZip($billCode, $productId, $folderId, $productName);
        }

        $requestedFileId = trim((string) $request->query('file', ''));
        $target = GoogleDriveLink::resolveDownloadTarget(
            $driveUrl,
            $requestedFileId !== '' ? $requestedFileId : null
        );
        if (!$target) {
            abort(404, 'Link Google Drive không hợp lệ hoặc thư mục chưa được chia sẻ công khai.');
        }
        $sourceUrl = $target['source_url'];

        try {
            $response = Http::timeout(180)
                ->withOptions(['allow_redirects' => ['max' => 10]])
                ->get($sourceUrl);
        } catch (\Throwable $e) {
            Log::error('Drive download failed', [
                'bill' => $billCode,
                'product' => $productId,
                'error' => $e->getMessage(),
            ]);
            abort(502, 'Không thể tải file từ Google Drive.');
        }

        if (!$response->successful()) {
            abort(502, 'Google Drive từ chối tải file (HTTP ' . $response->status() . ').');
        }

        $body = $response->body();
        if (stripos($body, '<html') !== false && stripos($body, 'Google Drive') !== false) {
            abort(502, 'File quá lớn hoặc cần xác nhận trên Google Drive. Hãy mở link gốc trong email đơn hàng.');
        }

        $contentType = $response->header('Content-Type') ?: 'application/octet-stream';
        $driveFileName = null;
        if ($folderId && !empty($target['file_id'])) {
            $driveFileName = GoogleDriveLink::folderFileName($folderId, $target['file_id']);
        }

        $filename = GoogleDriveLink::resolveDownloadFilename(
            $productName,
            $driveUrl,
            $contentType,
            $response->header('Content-Disposition'),
            $driveFileName
        );

        return response($body, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => GoogleDriveLink::attachmentContentDisposition($filename),
            'Cache-Control' => 'no-store',
        ]);
    }

    private function downloadPaidFolderZip($billCode, $productId, $folderId, $productName)
    {
        @set_time_limit(600);
        $files = GoogleDriveLink::listPublicFolderFiles($folderId);
        if ($files === []) {
            abort(404, 'Không tìm thấy file trong thư mục Google Drive.');
        }

        $zipResult = DriveFolderZipBuilder::build($files, $productName);
        if (!$zipResult) {
            Log::error('Drive folder zip failed', [
                'bill' => $billCode,
                'product' => $productId,
                'folder' => $folderId,
            ]);
            abort(502, 'Không thể đóng gói tài liệu. Vui lòng thử lại sau.');
        }

        return response()->download(
            $zipResult['path'],
            $zipResult['filename'],
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
    }

    public function checkout(){
            $data['cart'] = session()->get('cart', []);
            $data['profile'] = Auth::guard('customer')->user();
            return view('cart.checkout',$data);
        
    }
    public function postBill(Request $request){
        $profile = Auth::guard('customer')->user();
        if (!$profile) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán.');
        }

        $cart = session()->get('cart', []);
        if (count($cart) === 0) {
            return redirect()->route('listCart')->with('error', 'Giỏ hàng đang trống');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $this->getCartLinePrice($item) * (int) ($item['quantity'] ?? 1);
        }

        $code_bill = (int)(date('ymdHis') . rand(10, 99));
        DB::beginTransaction();
			try {
				$query = new Bill();
				$query->code_bill = $code_bill;
				$query->code_customer = $profile->id;
				$query->total_money = $subtotal;
				$query->statu = 0;
				$query->note = '';
                $query->payment_method = 'online';
                $query->cus_name = $profile->name;
                $query->cus_phone = $profile->phone;
                $query->cus_email = $profile->email;
                $query->cus_address = '';
                $query->transport_price = 0;
				$query->save();
                
					
                foreach($cart as $key => $item){
                    $billdetail = new BillDetail();
                    $billdetail->code_bill = $code_bill;
                    $billdetail->code_product = $item['idpro'];
                    $billdetail->name =$item['name'];
                    $billdetail->price = $this->getCartLinePrice($item);
                    $billdetail->qty = $item['quantity'];
                    $billdetail->images = $item['image'];
                    $billdetail->variant = $item['status_variant'] == 1 ? $item['variant'] : '';
                    $billdetail->save();
                }

                $checkoutUrl = $this->createPayosPaymentLink($query, $cart, $request);
                DB::commit();
                return redirect()->away($checkoutUrl);
			} catch (\Throwable $e) {
			    DB::rollBack();
                Log::error('Checkout failed', ['error' => $e->getMessage()]);
                return back()->with('error','Gửi đơn hàng thất bại: ' . $e->getMessage());
			}
    }

    public function payosReturn(Request $request)
    {
        $orderCode = (int)$request->get('orderCode', 0);
        $status = strtoupper((string)$request->get('status', ''));
        if ($orderCode > 0 && $status === 'PAID') {
            $bill = $this->markBillPaidByOrderCode($orderCode);
            if ($bill) {
                session()->forget('cart');
                return $this->renderOrderSuccess($bill);
            }
        }
        return redirect()->route('checkout')->with('error', 'Thanh toán chưa hoàn tất, vui lòng thử lại.');
    }

    public function payosCancel(Request $request)
    {
        $orderCode = (int)$request->get('orderCode', 0);
        if ($orderCode > 0) {
            $bill = Bill::where('code_bill', $orderCode)->first();
            if ($bill && (int)$bill->statu === 0) {
                $bill->statu = 2;
                $bill->save();
            }
        }
        return redirect()->route('checkout')->with('error', 'Bạn đã hủy thanh toán online.');
    }

    public function payosWebhook(Request $request)
    {
        try {
            $data = $request->input('data', []);
            $code = $request->input('code');
            if ((string)$code === '00' && isset($data['orderCode'])) {
                $this->markBillPaidByOrderCode((int)$data['orderCode']);
            }
        } catch (\Throwable $e) {
            Log::error('PayOS webhook error', ['error' => $e->getMessage()]);
        }
        return response()->json(['error' => 0, 'message' => 'ok']);
    }
    public function listCart(){
        $data['cart'] = session()->get('cart', []);
        return view('cart.list',$data);
    }
    private function getProductCoverImage(Product $product)
    {
        $images = json_decode($product->images ?? '[]', true);
        if (!is_array($images)) {
            return '';
        }

        $images = array_values(array_filter($images));

        return $images[0] ?? '';
    }

    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        $cart = session()->get('cart', []);
        

        if(isset($cart[$request->product_id])) {
            $cart[$request->product_id]['quantity'] = $cart[$request->product_id]['quantity'] + $request->quantity;
        } else {
            $cart[$request->product_id] = [
                "idpro" => $request->product_id,
                "name" => $product->name,
                "variant"=>$request->variant,
                "quantity" => $request->quantity,
                "price" => $request->price == 0 ? $product->price : $request->price,
                'status_variant' => $product->status_variant,
                "discount" => $product->discount,
                "image" => $this->getProductCoverImage($product),
            ];
        }
        session()->put('cart', $cart);
        return response()->json($cart);
    }
    public function update(Request $request)
    {
        if($request->id && $request->quantity){
            $cart = session()->get('cart');
            $cart[$request->id]["quantity"] = $request->quantity;
            session()->put('cart', $cart);
            return response()->json($cart);
        }
        
    }
    public function remove(Request $request)
    {
        if($request->id) {
            $cart = session()->get('cart');
            if(isset($cart[$request->id])) {
                unset($cart[$request->id]);
                session()->put('cart', $cart);
            }
            return response()->json($cart);
        }
    }
    public function orderSuccess(Request $request)
    {
        $downloads = session('last_order_downloads', []);
        $billCode = session('last_order_code');

        if (empty($downloads) && $request->filled('orderCode')) {
            $profile = Auth::guard('customer')->user();
            $bill = Bill::where('code_bill', (int) $request->get('orderCode'))
                ->when($profile, function ($q) use ($profile) {
                    $q->where('code_customer', $profile->id);
                })
                ->where('statu', 1)
                ->first();
            if ($bill) {
                $downloads = $this->buildOrderDownloadItems($bill);
                $billCode = $bill->code_bill;
            }
        }

        return view('cart.orderSuccess', [
            'downloads' => $downloads,
            'billCode' => $billCode,
        ]);
    }
}
