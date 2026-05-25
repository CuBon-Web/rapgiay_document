@extends('layouts.main.master')

@section('title')
Đổi mật khẩu
@endsection

@section('css')
<link href="{{ asset('frontend/css/account-orders.css') }}" rel="stylesheet">
@endsection

@section('content')
<section class="account-page">
    <div class="container">
        <nav class="account-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Trang chủ</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('accoungOrder') }}">Tài khoản</a>
            <span aria-hidden="true">/</span>
            <span>Đổi mật khẩu</span>
        </nav>

        <div class="account-layout">
            @include('auth.partials.account-sidebar', ['active' => 'password'])

            <main class="account-main">
                <header class="account-main__head">
                    <h1>Đổi mật khẩu</h1>
                    <p>Cập nhật mật khẩu đăng nhập để bảo vệ tài khoản của bạn.</p>
                </header>

                @include('auth.partials.alerts')

                <form action="{{ route('account.password.update') }}" method="post" class="account-form">
                    @csrf
                    <div class="account-form__field">
                        <label for="current_password">Mật khẩu hiện tại <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password" required
                            autocomplete="current-password" placeholder="Nhập mật khẩu hiện tại">
                    </div>
                    <div class="account-form__field">
                        <label for="password">Mật khẩu mới <span class="required">*</span></label>
                        <input type="password" id="password" name="password" required minlength="8"
                            autocomplete="new-password" placeholder="Ít nhất 8 ký tự">
                    </div>
                    <div class="account-form__field">
                        <label for="password_confirmation">Xác nhận mật khẩu mới <span class="required">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                            autocomplete="new-password" placeholder="Nhập lại mật khẩu mới">
                    </div>
                    <div class="account-form__actions">
                        <button type="submit" class="account-btn account-btn--primary">Lưu mật khẩu mới</button>
                        <a href="{{ route('accoungOrder') }}" class="account-btn account-btn--outline">Hủy</a>
                    </div>
                </form>

                <p class="account-form__hint">
                    Quên mật khẩu?
                    <a href="{{ route('password.forgot') }}">Đặt lại bằng mã OTP qua email</a>
                </p>
            </main>
        </div>
    </div>
</section>
@endsection
