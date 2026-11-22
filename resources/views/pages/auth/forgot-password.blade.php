@extends('layouts.app')
@section('title', 'Quên mật khẩu')
@section('content')
<div class="site-container auth-layout"><section class="auth-panel">
    <x-section-title title="Quên mật khẩu" description="Nhập email trong hồ sơ của bạn để nhận liên kết đặt lại mật khẩu." :level="1" />
    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf
        <x-input id="reset-email" name="email" label="Email đã đăng ký" type="email" :value="old('email')" autocomplete="email" required :show-error="true" />
        <x-button type="submit">Gửi liên kết</x-button>
    </form>
    <a class="text-action" href="{{ route('login') }}">Về đăng nhập</a>
</section></div>
@endsection
