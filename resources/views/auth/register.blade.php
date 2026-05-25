@extends('layouts.main.master')
@section('title')
Đăng ký tài khoản
@endsection
@section('description')
Tạo tài khoản để mua và tải tài liệu số
@endsection
@section('css')
<link href="{{ asset('frontend/css/auth.css') }}" rel="stylesheet">
@endsection
@section('content')
<section class="auth-page">
    <div class="container">
        <div class="auth-card auth-card--wide">
            <div class="auth-card__header">
                <h1>Tạo tài khoản mới</h1>
                <p>Đăng ký để mua tài liệu và quản lý đơn hàng</p>
            </div>
            <div class="auth-tabs">
                <a href="{{ route('login') }}">Đăng nhập</a>
                <a href="{{ route('register') }}" class="active">Đăng ký</a>
            </div>
            <div class="auth-card__body">
                @include('auth.partials.alerts')
                <form action="{{ route('postRegister') }}" method="post">
                    @csrf
                    <div class="auth-field">
                        <label for="name">Họ và tên <span class="required">*</span></label>
                        <input type="text" id="name" name="name" placeholder="Nhập họ và tên" required value="{{ old('name') }}">
                        @error('name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="auth-field">
                        <label for="phone">Số điện thoại <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại" required value="{{ old('phone') }}">
                        @error('phone')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="auth-field">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="Nhập email" required value="{{ old('email') }}">
                        @error('email')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="auth-field auth-field--password">
                        <label for="password">Mật khẩu <span class="required">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Tối thiểu 8 ký tự" required>
                        <button type="button" class="auth-toggle-pass" data-target="password" aria-label="Hiện mật khẩu">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                        @error('password')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="auth-field auth-field--password">
                        <label for="password_confirmation">Xác nhận mật khẩu <span class="required">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu" required>
                        <button type="button" class="auth-toggle-pass" data-target="password_confirmation" aria-label="Hiện mật khẩu">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <button type="submit" class="auth-submit">Tạo tài khoản</button>
                </form>
                <p class="auth-footer-text">Đã có tài khoản? <a href="{{ route('login') }}" class="auth-link">Đăng nhập</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
@section('js')
<script>
document.querySelectorAll('.auth-toggle-pass').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var input = document.getElementById(btn.dataset.target);
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    });
});
</script>
@endsection
