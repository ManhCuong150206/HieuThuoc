@extends('layouts.app')
@section('title', 'Đăng ký tài khoản')
@section('content')
<div class="site-container auth-layout">
    <section class="auth-panel" aria-labelledby="register-title">
        <div class="section-title"><h1 id="register-title">Tạo tài khoản An Tâm</h1><p class="section-description">Đăng ký để bắt đầu chăm sóc sức khỏe cùng An Tâm.</p></div>
        <form class="auth-form" method="POST" action="{{ route('register.submit') }}">
            @csrf
            <x-input id="register-full-name" name="full_name" label="Họ và tên" placeholder="Nhập họ và tên" :value="old('full_name')" autocomplete="name" required :show-error="true" />
            <x-input id="register-username" name="username" label="Tên đăng nhập" placeholder="Nhập tên đăng nhập" :value="old('username')" autocomplete="username" hint="Tối đa 50 ký tự: chữ không dấu, số, dấu chấm, gạch dưới hoặc gạch ngang." required :show-error="true" />
            <x-input id="register-email" name="email" label="Email" type="email" placeholder="Nhập địa chỉ email" :value="old('email')" autocomplete="email" required :show-error="true" />
            <x-input id="register-phone" name="phone" label="Số điện thoại" type="tel" placeholder="Nhập số điện thoại" :value="old('phone')" autocomplete="tel" required :show-error="true" />
            <x-input id="register-password" name="password" label="Mật khẩu" type="password" placeholder="Tạo mật khẩu" autocomplete="new-password" hint="Mật khẩu từ 8 đến 72 ký tự." required :show-error="true" />
            <x-input id="register-password-confirmation" name="password_confirmation" label="Nhập lại mật khẩu" type="password" placeholder="Nhập lại mật khẩu" autocomplete="new-password" required :show-error="true" />
            <x-button type="submit" class="full-width">Đăng ký tài khoản</x-button>
        </form>
        <p class="login-signup">Đã có tài khoản? &nbsp;<a href="{{ route('login') }}"><strong>Đăng nhập ngay</strong></a></p>
        <p class="login-terms">Đăng ký đồng nghĩa bạn đồng ý với Điều khoản sử dụng và Chính sách bảo mật.</p>
    </section>
</div>
@endsection
