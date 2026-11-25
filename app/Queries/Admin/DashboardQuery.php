<?php

namespace App\Queries\Admin;

use App\Models\Account;
use App\Models\Order;
use App\Models\Product;

class DashboardQuery
{
    public function summary(): array
    {
        $lastDay = today();
        $firstDay = $lastDay->copy()->subDays(6);
        $unitsByDate = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->fulfilled()
            ->whereBetween('orders.created_at', [$firstDay->toDateString(), $lastDay->toDateString()])
            ->groupBy('orders.created_at')
            ->selectRaw('orders.created_at AS order_date, SUM(order_items.quantity) AS units_sold')
            ->pluck('units_sold', 'order_date');

        $dailySales = collect($firstDay->daysUntil($lastDay))->map(fn ($date) => [
            'date' => $date,
            'units' => (int) $unitsByDate->get($date->toDateString(), 0),
        ]);

        return [
            'revenue' => Order::where('status', '!=', 'CANCELLED')->sum('total_amount'),
            'orderCount' => Order::count(), 'productCount' => Product::count(),
            'customerCount' => Account::whereRaw('UPPER(role) = ?', ['CUSTOMER'])->count(),
            'orders' => Order::orderByDesc('id')->limit(8)->get(),
            'dailySales' => $dailySales,
            'peakDailySales' => $dailySales->max('units'),
        ];
    }
}
