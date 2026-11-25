<?php

namespace App\Services;

use App\Models\Account;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function create(Account $account, OrderItem $item, array $data): void
    {
        DB::transaction(function () use ($account, $item, $data): void {
            $order = $item->order()->lockForUpdate()->firstOrFail();
            abort_unless($order->belongsToAccount($account), 404);
            if ($order->status !== 'COMPLETED') {
                throw ValidationException::withMessages(['review' => 'Chỉ được đánh giá đơn hàng đã hoàn thành.']);
            }
            if ($item->review()->exists()) {
                throw ValidationException::withMessages(['review' => 'Sản phẩm trong đơn này đã được đánh giá.']);
            }
            $item->review()->create($data + ['status' => 'VISIBLE', 'created_at' => today()]);
        });
    }
}
