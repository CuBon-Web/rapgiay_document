@extends('layouts.main.master')
@section('title')
Đặt lại mật khẩu
@endsection
@section('description')
Nhập mã OTP và mật khẩu mới
@endsection
@section('css')
<link href="{{ asset('frontend/css/auth.css') }}" rel="stylesheet">
@endsection
@section('content')
<section class="auth-page">
    <div class="container">
        <div class="auth-card auth-card--wide">
            <div class="auth-card__header">
                <h1>Đặt lại mật khẩu</h1>
                <p>Sao chép mã OTP từ email và nhập mật khẩu mới</p>
                <span class="auth-email-badge">{{ $email }}</span>
            </div>
            <div class="auth-card__body">
                @include('auth.partials.alerts')
                <form action="{{ route('password.reset.submit') }}" method="post" id="reset-password-form">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="otp" id="otp-hidden" value="{{ old('otp') }}">

                    <label class="auth-field">
                        <span style="display:block;font-size:14px;font-weight:600;color:#334155;margin-bottom:8px;">Mã OTP <span class="required">*</span></span>
                        <div class="auth-otp-group" id="otp-inputs">
                            @for ($i = 0; $i < 6; $i++)
                                <input type="text" class="auth-otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" data-index="{{ $i }}">
                            @endfor
                        </div>
                        <p class="auth-otp-hint">Bạn có thể dán (Ctrl+V) toàn bộ mã 6 số từ email</p>
                    </label>

                    <div class="auth-field auth-field--password">
                        <label for="password">Mật khẩu mới <span class="required">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Tối thiểu 8 ký tự" required>
                        <button type="button" class="auth-toggle-pass" data-target="password" aria-label="Hiện mật khẩu">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <div class="auth-field auth-field--password">
                        <label for="password_confirmation">Xác nhận mật khẩu mới <span class="required">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu" required>
                        <button type="button" class="auth-toggle-pass" data-target="password_confirmation" aria-label="Hiện mật khẩu">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <button type="submit" class="auth-submit">Đặt lại mật khẩu</button>
                </form>
                <div class="auth-resend">
                    Không nhận được mã?
                    <form action="{{ route('password.forgot.send') }}" method="post" style="display:inline;">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <button type="submit" class="auth-link" style="background:none;border:none;padding:0;cursor:pointer;">Gửi lại OTP</button>
                    </form>
                </div>
                <p class="auth-footer-text"><a href="{{ route('login') }}" class="auth-link">Quay lại đăng nhập</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
@section('js')
<script>
(function() {
    var inputs = document.querySelectorAll('.auth-otp-input');
    var hidden = document.getElementById('otp-hidden');
    var form = document.getElementById('reset-password-form');

    function syncHidden() {
        hidden.value = Array.prototype.map.call(inputs, function(el) { return el.value; }).join('');
    }

    function fillFromString(str) {
        var digits = (str || '').replace(/\D/g, '').slice(0, 6);
        inputs.forEach(function(input, i) {
            input.value = digits[i] || '';
        });
        syncHidden();
        var focusIndex = Math.min(digits.length, 5);
        if (inputs[focusIndex]) inputs[focusIndex].focus();
    }

    if (hidden.value && hidden.value.length === 6) {
        fillFromString(hidden.value);
    }

    inputs.forEach(function(input, index) {
        input.addEventListener('input', function() {
            input.value = input.value.replace(/\D/g, '').slice(0, 1);
            syncHidden();
            if (input.value && inputs[index + 1]) {
                inputs[index + 1].focus();
            }
        });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !input.value && inputs[index - 1]) {
                inputs[index - 1].focus();
            }
        });
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text');
            fillFromString(text);
        });
    });

    form.addEventListener('submit', function(e) {
        syncHidden();
        if (hidden.value.length !== 6) {
            e.preventDefault();
            alert('Vui lòng nhập đủ mã OTP 6 chữ số.');
            inputs[0].focus();
        }
    });

    document.querySelectorAll('.auth-toggle-pass').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var el = document.getElementById(btn.dataset.target);
            var icon = btn.querySelector('i');
            if (el.type === 'password') {
                el.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                el.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        });
    });
})();
</script>
@endsection
