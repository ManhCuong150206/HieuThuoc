@extends('layouts.app')
@section('title', 'Đăng nhập')
@section('content')
<div class="site-container auth-layout">
    <section class="auth-panel" aria-labelledby="login-title">
        <div class="section-title"><h1 id="login-title">Chào mừng bạn trở lại</h1><p class="section-description">Đăng nhập để tiếp tục cùng An Tâm.</p></div>
        <form class="auth-form" method="POST" action="{{ route('login.submit') }}">
            @csrf
            <x-input id="login-identity" name="username" label="Tên đăng nhập" placeholder="Nhập tên đăng nhập" :value="old('username')" autocomplete="username" required />
            <x-input id="login-password" name="password" label="Mật khẩu" type="password" placeholder="Nhập mật khẩu" autocomplete="current-password" required />
            <div class="login-options">
                <a class="text-action" href="{{ route('password.request') }}">Quên mật khẩu?</a>
            </div>
            <x-button type="submit" class="full-width">Đăng nhập</x-button>
        </form>
        <p class="login-signup">Chưa có tài khoản? &nbsp;<a href="{{ route('register') }}"><strong>Đăng ký ngay</strong></a></p>
        <p class="login-terms">Đăng nhập đồng nghĩa bạn đồng ý với Điều khoản sử dụng và Chính sách bảo mật.</p>
    </section>
</div>
@endsection
