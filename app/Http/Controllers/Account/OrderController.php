<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $account = $request->user();
        $profile = $account->profile;
        $orders = Order::with(['items' => fn ($items) => $items->orderBy('id'), 'items.product', 'items.review'])
            ->where('user_id', $profile?->id)
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(10, pageName: 'orders_page');

        return view('pages.account.orders', [
            'account' => $account,
            'profile' => $profile,
            'orders' => $orders,
            'statusLabels' => OrderService::STATUS_LABELS,
        ]);
    }

    public function cancel(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        abort_unless($order->belongsToAccount($request->user()), 404);
        $orders->transition($order, 'CANCELLED', true);

        return back()->with('status', 'Đã hủy đơn và hoàn lại tồn kho.');
    }

    public function confirmDelivery(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        abort_unless($order->belongsToAccount($request->user()), 404);
        $orders->transition($order, 'DELIVERED', true);

        return back()->with('status', 'Đã xác nhận nhận hàng.');
    }

    public function reorder(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        abort_unless($order->belongsToAccount($request->user()), 404);
        $orders->reorder($request->user(), $order);
        $request->session()->forget('checkout_token');

        return redirect()->route('cart.index')->with('status', 'Đã thêm lại các sản phẩm còn bán vào giỏ hàng.');
    }
}
