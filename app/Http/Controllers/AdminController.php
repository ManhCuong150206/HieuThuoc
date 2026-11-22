<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.admin.dashboard', [
            'revenue' => Order::where('status', '!=', 'CANCELLED')->sum('total_amount'),
            'orderCount' => Order::count(), 'productCount' => Product::count(),
            'customerCount' => Account::whereRaw('UPPER(role) = ?', ['CUSTOMER'])->count(),
            'orders' => Order::orderByDesc('id')->limit(8)->get(),
            'lowStock' => Product::where('is_active', true)->where('quantity', '<=', 5)->orderBy('quantity')->limit(8)->get(),
            'expiring' => Product::where('is_active', true)->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', today()->addDays(30))->orderBy('expiry_date')->limit(8)->get(),
            'statusLabels' => OrderService::STATUS_LABELS,
        ]);
    }

    public function orders(Request $request): View
    {
        $data = $request->validate(['status' => ['nullable', Rule::in(array_keys(OrderService::STATUS_LABELS))], 'q' => ['nullable', 'string', 'max:100']]);

        return view('pages.admin.orders', [
            'orders' => Order::with('items', 'payments')->when($data['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
                ->when($data['q'] ?? null, fn ($q, $text) => $q->where('order_code', 'like', '%'.$text.'%'))
                ->orderByDesc('id')->paginate(15)->withQueryString(),
            'statusLabels' => OrderService::STATUS_LABELS,
        ]);
    }

    public function updateOrder(Request $request, Order $order, OrderService $service): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(OrderService::STATUS_LABELS))]]);
        $service->transition($order, $data['status']);

        return back()->with('status', 'Đã cập nhật đơn hàng.');
    }

    public function customers(): View
    {
        return view('pages.admin.customers', ['accounts' => Account::with('profile')->orderByDesc('id')->paginate(20)]);
    }

    public function updateCustomer(Request $request, Account $account): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        abort_if($account->isAdmin() || strtolower($account->role) === 'admin', 403);
        $account->is_active = (bool) $data['is_active'];
        $account->save();

        return back()->with('status', 'Đã cập nhật trạng thái tài khoản.');
    }

    public function reviews(): View
    {
        return view('pages.admin.reviews', ['reviews' => Review::with('orderItem.product', 'orderItem.order.user')->orderByDesc('id')->paginate(20)]);
    }

    public function updateReview(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['PENDING', 'VISIBLE', 'HIDDEN'])]]);
        $review->update($data);

        return back()->with('status', 'Đã cập nhật đánh giá.');
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
