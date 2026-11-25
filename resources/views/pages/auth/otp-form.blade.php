<x-section-title title="Xác minh mã OTP" :description="'Nhập mã 6 số đã gửi đến '.$email.'.'" :level="1" />
<form method="POST" action="{{ route('password.verify') }}" class="auth-form otp-form">
    @csrf
    @include('pages.auth.status', ['statusMessage' => $message ?? session('status')])
    <div class="ui-field otp-field">
        <label for="reset-otp">Mã OTP</label>
        <div class="otp-entry" data-otp-entry>
            <input id="reset-otp" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required
                @error('otp') aria-invalid="true" aria-describedby="reset-otp-error" @enderror>
            <div class="otp-slots" aria-hidden="true">
                @for ($digit = 0; $digit < 6; $digit++)
                    <span></span>
                @endfor
            </div>
        </div>
        @error('otp')<p id="reset-otp-error" class="auth-field-error">{{ $message }}</p>@enderror
    </div>
    <x-button type="submit">Xác minh mã OTP</x-button>
    <a class="text-action" href="{{ route('password.request') }}">Yêu cầu mã OTP mới</a>
</form>
