@extends('layouts.main.master')
@section('title')
Đăng nhập
@endsection
@section('description')
Đăng nhập tài khoản để mua và tải tài liệu số
@endsection
@section('css')
<link href="{{ asset('frontend/css/auth.css') }}" rel="stylesheet">
@endsection
@section('content')
<section class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div class="auth-card__header">
                <h1>Tài khoản của bạn</h1>
                <p>Đăng nhập để mua tài liệu và theo dõi đơn hàng</p>
            </div>
            <div class="auth-tabs">
                <a href="{{ route('login') }}" class="active">Đăng nhập</a>
                <a href="{{ route('register') }}">Đăng ký</a>
            </div>
            <div class="auth-card__body">
                @include('auth.partials.alerts')
                <form action="{{ route('postlogin') }}" method="post">
                    @csrf
                    @php $redirectTo = old('intended', $intended ?? ''); @endphp
                    @if ($redirectTo)
                        <input type="hidden" name="intended" value="{{ $redirectTo }}">
                    @endif
                    <div class="auth-field">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="Nhập email" required
                            value="{{ old('email') }}" autocomplete="email">
                    </div>
                    <div class="auth-field auth-field--password">
                        <label for="password">Mật khẩu <span class="required">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required
                            autocomplete="current-password">
                        <button type="button" class="auth-toggle-pass" data-target="password" aria-label="Hiện mật khẩu">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <div class="auth-row">
                        <label class="auth-remember">
                            <input type="checkbox" name="remember" value="1">
                            Ghi nhớ đăng nhập
                        </label>
                        <a href="{{ route('password.forgot') }}" class="auth-link">Quên mật khẩu?</a>
                    </div>
                    <button type="submit" class="auth-submit">Đăng nhập</button>
                </form>
                <p class="auth-footer-text">Chưa có tài khoản? <a href="{{ route('register') }}" class="auth-link">Đăng ký ngay</a></p>
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
