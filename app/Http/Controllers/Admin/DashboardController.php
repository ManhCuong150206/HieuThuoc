<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Queries\Admin\DashboardQuery;
use App\Services\OrderService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __invoke(DashboardQuery $dashboard): View
    {
        return view('pages.admin.dashboard', [
            ...$dashboard->summary(),
            'statusLabels' => OrderService::STATUS_LABELS,
        ]);
    }

    public function report(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Mã đơn', 'Ngày', 'Người nhận', 'Số điện thoại', 'Tổng tiền', 'Trạng thái']);
            foreach (Order::orderBy('id')->cursor() as $order) {
                $cells = [$order->order_code, $order->created_at->toDateString(), $order->recipient_name,
                    $order->recipient_phone, $order->total_amount, OrderService::STATUS_LABELS[$order->status] ?? $order->status];
                fputcsv($stream, array_map(fn ($value) => preg_match('/^[=+@\-\t\r]/u', (string) $value) ? "'".$value : $value, $cells));
            }
            fclose($stream);
        }, 'don-hang-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
