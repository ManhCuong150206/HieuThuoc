<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PasswordResetMail extends Mailable
{
    public function __construct(public string $resetUrl) {}

    public function build(): static
    {
        return $this->subject('Đặt lại mật khẩu An Tâm Pharmacy')->view('emails.reset-password');
    }
}
