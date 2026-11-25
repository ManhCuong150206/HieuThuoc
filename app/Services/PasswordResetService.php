<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Models\CustomerProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PasswordResetService
{
    public const OTP_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    public function sendCode(string $email): string
    {
        [$email, $otp, $digest] = DB::transaction(function () use ($email): array {
            $profile = CustomerProfile::where('email', $email)->lockForUpdate()->first();
            if (! $profile?->account?->is_active) {
                throw ValidationException::withMessages(['email' => 'Tài khoản không tồn tại.']);
            }

            $email = $profile->email;
            $recent = DB::table('password_reset_tokens')->where('email', $email)->first();
            if ($recent?->created_at && Carbon::parse($recent->created_at)->gt(now()->subMinute())) {
                throw ValidationException::withMessages(['email' => 'Vui lòng chờ 1 phút trước khi yêu cầu mã mới.']);
            }

            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $digest = $this->otpDigest($email, $otp);
            DB::table('password_reset_tokens')->updateOrInsert(['email' => $email], [
                'token' => $digest, 'created_at' => now(), 'attempts' => 0,
            ]);

            return [$email, $otp, $digest];
        });

        try {
            Mail::to($email)->send(new PasswordResetMail($otp, self::OTP_MINUTES));
        } catch (TransportExceptionInterface) {
            DB::table('password_reset_tokens')->where('email', $email)->where('token', $digest)->delete();
            logger()->warning('Password reset OTP could not be sent by the configured mail transport.');

            throw ValidationException::withMessages(['email' => 'Chưa gửi được mã OTP. Vui lòng thử lại.']);
        }

        return $email;
    }

    public function verifyCode(string $email, string $otp): string
    {
        $grant = Str::random(64);
        $verified = DB::transaction(function () use ($email, $otp, $grant): bool {
            $codes = DB::table('password_reset_tokens')->where('email', $email);
            $reset = $codes->lockForUpdate()->first();
            if (! $reset?->created_at || Carbon::parse($reset->created_at)->lte(now()->subMinutes(self::OTP_MINUTES))
                || $reset->attempts >= self::MAX_ATTEMPTS) {
                return false;
            }
            if (! hash_equals($reset->token, $this->otpDigest($email, $otp))) {
                $codes->increment('attempts');

                return false;
            }

            // Replace the OTP with a one-time grant that only this session can use.
            $codes->update(['token' => hash('sha256', $grant), 'created_at' => now()]);

            return true;
        });
        if (! $verified) {
            throw ValidationException::withMessages(['otp' => 'Mã OTP không hợp lệ, đã hết hạn hoặc đã vượt số lần thử. Bạn có thể yêu cầu mã mới.']);
        }

        return $grant;
    }

    public function reset(string $email, string $grant, string $password): ?string
    {
        return DB::transaction(function () use ($email, $grant, $password): ?string {
            $profile = CustomerProfile::where('email', $email)->lockForUpdate()->first();
            $codes = DB::table('password_reset_tokens')->where('email', $email);
            $reset = $codes->lockForUpdate()->first();
            if (! $profile || ! $reset?->created_at || Carbon::parse($reset->created_at)->lte(now()->subMinutes(self::OTP_MINUTES))
                || ! hash_equals($reset->token, hash('sha256', $grant))) {
                return null;
            }

            $account = $profile->account()->lockForUpdate()->first();
            if (! $account?->is_active) {
                return null;
            }
            $account->changePassword($password);
            $codes->delete();

            return $account->username;
        });
    }

    private function otpDigest(string $email, string $otp): string
    {
        return hash_hmac('sha256', "password-reset\0".$email."\0".$otp, (string) config('app.key'));
    }
}
