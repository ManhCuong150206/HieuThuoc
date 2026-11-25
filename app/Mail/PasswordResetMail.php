<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PasswordResetMail extends Mailable
{
    public function __construct(public string $otp, public int $expiresInMinutes) {}

    public function build(): static
    {
        return $this->subject('Mã OTP đặt lại mật khẩu An Tâm Pharmacy')->view('emails.reset-password');
    }
}
