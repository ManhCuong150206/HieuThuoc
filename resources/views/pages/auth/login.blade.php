@extends('layouts.app')
@section('title', 'Đăng nhập')
@section('content')
<div class="site-container auth-layout">
    <section class="auth-welcome">
        <x-badge>AN TÂM ĐỒNG HÀNH</x-badge>
        <h2>Một tài khoản.<br>Trọn vẹn chăm sóc.</h2>
        <p>Lưu sản phẩm yêu thích, theo dõi đơn hàng và mua lại những sản phẩm bạn tin dùng.</p>
        <div class="auth-art"><x-icon name="login-art" /></div>
    </section>
    <section class="auth-panel" aria-labelledby="login-title">
        <div class="section-title"><h1 id="login-title">Chào mừng bạn trở lại</h1><p class="section-description">Đăng nhập để tiếp tục cùng An Tâm.</p></div>
        <x-input id="login-identity" label="Email hoặc số điện thoại" placeholder="Nhập email hoặc số điện thoại" />
        <x-input id="login-password" label="Mật khẩu" type="password" placeholder="Nhập mật khẩu" />
        <div class="login-options">
            <label class="check-label"><input type="checkbox"> Ghi nhớ đăng nhập</label>
            <span>Quên mật khẩu?</span>
        </div>
        <x-button class="full-width">Đăng nhập</x-button>
        <p class="login-signup">Chưa có tài khoản? &nbsp;<strong>Đăng ký ngay</strong></p>
        <p class="login-terms">Đăng nhập đồng nghĩa bạn đồng ý với Điều khoản sử dụng và Chính sách bảo mật.</p>
    </section>
</div>
@endsection
