<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, OrderItem $item): RedirectResponse
    {
        $data = $request->validate(['rating' => ['required', 'integer', 'between:1,5'], 'comment' => ['nullable', 'string', 'max:500']]);
        DB::transaction(function () use ($request, $item, $data): void {
            $order = $item->order()->lockForUpdate()->firstOrFail();
            abort_unless($order->user_id === $request->user()->profile?->id, 404);
            if ($order->status !== 'COMPLETED') {
                throw ValidationException::withMessages(['review' => 'Chỉ được đánh giá đơn hàng đã hoàn thành.']);
            }
            if ($item->review()->exists()) {
                throw ValidationException::withMessages(['review' => 'Sản phẩm trong đơn này đã được đánh giá.']);
            }
            $item->review()->create($data + ['status' => 'PENDING', 'created_at' => today()]);
        });

        return back()->with('status', 'Đã gửi đánh giá, đang chờ duyệt.');
    }
}
