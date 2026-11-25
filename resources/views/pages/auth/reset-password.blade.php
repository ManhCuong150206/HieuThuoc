@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')
@section('inline-status', '1')
@section('inline-validation', '1')
@section('content')
<div class="site-container auth-layout"><section class="auth-panel">
    @if ($verified)
    <x-section-title title="Đặt mật khẩu mới" :level="1" />
    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        @include('pages.auth.status')
        <x-input id="reset-password" name="password" label="Mật khẩu mới (ít nhất 8 ký tự)" type="password" autocomplete="new-password" required :show-error="true" />
        <x-input id="reset-confirm" name="password_confirmation" label="Nhập lại mật khẩu" type="password" autocomplete="new-password" required />
        <x-button type="submit">Lưu mật khẩu mới</x-button>
    </form>
    @else
        @include('pages.auth.otp-form')
    @endif
</section></div>
@endsection
