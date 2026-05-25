@extends('layouts.main.master')
@section('title')
Quên mật khẩu
@endsection
@section('description')
Yêu cầu mã OTP để đặt lại mật khẩu
@endsection
@section('css')
<link href="{{ asset('frontend/css/auth.css') }}" rel="stylesheet">
@endsection
@section('content')
<section class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div class="auth-card__header">
                <h1>Quên mật khẩu</h1>
                <p>Nhập email đăng ký, chúng tôi sẽ gửi mã OTP 6 chữ số</p>
            </div>
            <div class="auth-card__body">
                @include('auth.partials.alerts')
                <form action="{{ route('password.forgot.send') }}" method="post">
                    @csrf
                    <div class="auth-field">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="Nhập email đã đăng ký" required
                            value="{{ old('email') }}" autocomplete="email">
                    </div>
                    <button type="submit" class="auth-submit">Gửi mã OTP</button>
                </form>
                <p class="auth-footer-text"><a href="{{ route('login') }}" class="auth-link">Quay lại đăng nhập</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
