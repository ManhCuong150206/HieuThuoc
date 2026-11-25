@extends('layouts.app')
@section('title', 'Quên mật khẩu')
@section('inline-validation', '1')
@section('content')
<div class="site-container auth-layout"><section class="auth-panel">
    <x-section-title title="Quên mật khẩu" description="Nhập email đã đăng ký để nhận mã OTP đặt lại mật khẩu." :level="1" />
    <form method="POST" action="{{ route('password.email') }}" class="auth-form" data-password-email>
        @csrf
        <div class="ui-field">
            <label for="reset-email">Email đã đăng ký</label>
            <input id="reset-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required
                aria-describedby="reset-email-error" @error('email') aria-invalid="true" @enderror>
            <p class="auth-field-error" id="reset-email-error" role="alert" @if (!$errors->has('email')) hidden @endif>{{ $errors->first('email') }}</p>
        </div>
        <x-button type="submit">Gửi mã OTP</x-button>
        <p class="login-signup">Bạn đã có tài khoản? <a class="text-action" href="{{ route('login') }}"><strong>Đăng nhập ngay</strong></a>.</p>
    </form>
</section></div>
@endsection
