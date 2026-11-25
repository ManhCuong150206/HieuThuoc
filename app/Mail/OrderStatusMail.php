<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;

class OrderStatusMail extends Mailable
{
    public function __construct(
        public Order $order,
        public string $mailSubject,
        public string $statusMessage,
    ) {}

    public function build(): static
    {
        return $this->subject($this->mailSubject)->view('emails.order-status');
    }
}
