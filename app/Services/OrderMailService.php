<?php

namespace App\Services;

use App\Mail\OrderStatusMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class OrderMailService
{
    private const MESSAGES = [
        'CONFIRMED' => [
            'subject' => 'Đặt hàng thành công - Đơn #%s',
            'message' => 'Bạn đã đặt hàng thành công. Chúng tôi sẽ sớm xử lý và giao đơn cho bạn.',
        ],
        'SHIPPING' => [
            'subject' => 'Đơn hàng #%s đang trên đường tới bạn',
            'message' => 'Đơn hàng của bạn đang trên đường tới bạn, vui lòng chú ý điện thoại.',
        ],
        'DELIVERED' => [
            'subject' => 'Giao hàng thành công - Đơn #%s',
            'message' => 'Đơn hàng đã được giao thành công. Cảm ơn bạn đã mua hàng.',
        ],
    ];

    public function send(Order $order, string $status): void
    {
        $message = self::MESSAGES[$status] ?? null;
        if ($message === null) {
            return;
        }

        try {
            $order->load('user');
            $email = trim($order->user?->email ?? '');
            if ($email === '') {
                return;
            }

            $order->load('items.product');
            $this->deliver($email, new OrderStatusMail(
                $order,
                sprintf($message['subject'], $order->order_code),
                $message['message'],
            ));
        } catch (Throwable $exception) {
            Log::error('Không thể gửi email thông báo đơn hàng.', [
                'order_id' => $order->id,
                'status' => $order->status,
                'exception' => $exception,
            ]);
        }
    }

    private function deliver(string $email, OrderStatusMail $mail): void
    {
        try {
            Mail::to($email)->send($mail);
        } catch (TransportExceptionInterface $exception) {
            // Only retry a dropped handshake before the SMTP envelope was sent.
            $smtpDebug = $exception->getDebug();
            $startedHandshake = str_contains($smtpDebug, '] > EHLO ') || str_contains($smtpDebug, '] > HELO ');
            if (! str_ends_with($exception->getMessage(), 'has been closed unexpectedly.')
                || ! $startedHandshake
                || str_contains($smtpDebug, '] > MAIL FROM:')) {
                throw $exception;
            }

            Mail::purge();
            Mail::to($email)->send($mail);
        }
    }
}
