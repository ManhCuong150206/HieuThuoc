@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')
@section('content')
<div class="site-container auth-layout"><section class="auth-panel">
    <x-section-title title="Đặt lại mật khẩu" :level="1" />
    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf<input type="hidden" name="token" value="{{ $token }}">
        <x-input id="reset-email" name="email" label="Email" type="email" :value="old('email', $email)" autocomplete="email" required :show-error="true" />
        <x-input id="reset-password" name="password" label="Mật khẩu mới (ít nhất 8 ký tự)" type="password" autocomplete="new-password" required />
        <x-input id="reset-confirm" name="password_confirmation" label="Nhập lại mật khẩu" type="password" autocomplete="new-password" required />
        <x-button type="submit">Lưu mật khẩu mới</x-button>
    </form>
</section></div>
@endsection
