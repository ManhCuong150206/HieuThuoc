@extends('layouts.account', ['accountPageTitle' => 'Thông tin cá nhân'])

@section('account-content')
    <section class="account-panel" id="thong-tin-ca-nhan" aria-label="Hồ sơ cá nhân">
        <form class="profile-form" method="POST" action="{{ route('account.update') }}">
            @csrf
            @method('PUT')
            <div class="field-pair profile-name">
                <x-input id="profile-name" name="full_name" label="Họ và tên" :value="old('full_name', $profile?->full_name)" autocomplete="name" required :show-error="true" />
                <x-input id="profile-phone" name="phone" label="Số điện thoại" type="tel" :value="old('phone', $profile?->phone)" autocomplete="tel" required :show-error="true" />
            </div>
            <x-input id="profile-email" name="email" label="Email" type="email" :value="old('email', $profile?->email)" autocomplete="email" required :show-error="true" />
            <x-input id="profile-address" name="address" label="Địa chỉ" :value="old('address', $profile?->address)" autocomplete="street-address" placeholder="Nhập địa chỉ của bạn" :show-error="true" />
            <div class="profile-actions"><x-button type="submit">Lưu thay đổi</x-button></div>
        </form>

        <details class="account-password" id="doi-mat-khau" @if ($errors->hasAny(['current_password', 'password'])) open @endif>
            <summary>Đổi mật khẩu</summary>
            <form method="POST" action="{{ route('account.password') }}" class="profile-form">
                @csrf
                @method('PUT')
                <x-input id="current-password" name="current_password" type="password" label="Mật khẩu hiện tại" autocomplete="current-password" required :show-error="true" />
                <x-input id="new-password" name="password" type="password" label="Mật khẩu mới (ít nhất 8 ký tự)" autocomplete="new-password" required :show-error="true" />
                <x-input id="confirm-password" name="password_confirmation" type="password" label="Nhập lại mật khẩu mới" autocomplete="new-password" required />
                <div><x-button type="submit">Đổi mật khẩu</x-button></div>
            </form>
        </details>
    </section>
@endsection
