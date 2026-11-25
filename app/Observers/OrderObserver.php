<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\OrderMailService;

class OrderObserver
{
    public function __construct(private OrderMailService $orderMail) {}

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $mailStatus = $order->status;
        if ($mailStatus === 'COMPLETED' && $order->getOriginal('status') === 'SHIPPING') {
            $mailStatus = 'DELIVERED';
        }
        if (! in_array($mailStatus, ['SHIPPING', 'DELIVERED'], true)) {
            return;
        }

        $snapshot = clone $order;
        $order->getConnection()->afterCommit(fn () => $this->orderMail->send($snapshot, $mailStatus));
    }
}
