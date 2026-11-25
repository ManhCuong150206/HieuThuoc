<?php

namespace App\Queries\Admin;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderQuery
{
    public function paginate(array $data): LengthAwarePaginator
    {
        $query = Order::with('items', 'payments')
            ->when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($data['q'] ?? null, fn ($query, $text) => $query->where('order_code', 'like', '%'.$text.'%'))
            ->orderByDesc('id');
        $orders = $query->paginate(15, ['*'], 'page', $data['page'] ?? 1);
        if ($orders->currentPage() > $orders->lastPage()) {
            $orders = $query->paginate(15, ['*'], 'page', $orders->lastPage(), $orders->total());
        }

        return $orders;
    }
}
