<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Promotion;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request, CartService $cart): View|RedirectResponse
    {
        $profile = $request->user()->profile;
        if (! $profile) {
            return redirect()->route('account.index')->withErrors(['cart' => 'Vui lòng cập nhật thông tin cá nhân trước khi mua hàng.']);
        }
        $items = $cart->itemsFor($request->user());
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Giỏ hàng đang trống.']);
        }
        if (! $request->session()->has('checkout_token')) {
            $request->session()->put('checkout_token', Str::random(48));
        }

        return view('pages.checkout.index', [
            'profile' => $profile, 'items' => $items,
            'subtotal' => $items->sum(fn ($item) => (int) round($item->price) * $item->quantity),
            'shippingFee' => OrderService::SHIPPING_FEE,
            'promotions' => Promotion::where('is_active', true)->whereDate('starts_at', '<=', today())
                ->whereDate('ends_at', '>=', today())->orderBy('name')->get(),
            'checkoutToken' => $request->session()->get('checkout_token'),
        ]);
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9 .()-]{8,18}[0-9]$/'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(['COD'])],
            'promotion_id' => ['nullable', 'integer'],
            'checkout_token' => ['required', 'string', 'size:48'],
            'terms' => ['accepted'],
        ], [
            'payment_method.in' => 'Thanh toán mã QR đang bổ sung. Vui lòng chọn tiền mặt.',
            'recipient_phone.regex' => 'Số điện thoại không hợp lệ.',
            'terms.accepted' => 'Vui lòng đồng ý với điều khoản mua hàng.',
        ]);
        abort_unless(hash_equals((string) $request->session()->get('checkout_token', ''), $data['checkout_token']), 419);
        $order = $orders->place($request->user(), $data, $data['checkout_token']);
        $request->session()->put('last_order_id', $order->id);

        return redirect()->route('checkout.success', $order);
    }

    public function success(Request $request, ?Order $order = null): View|RedirectResponse
    {
        if (! $order) {
            $order = Order::find($request->session()->get('last_order_id'));
        }
        if (! $order) {
            return redirect()->route('account.index');
        }
        abort_unless($order->user_id === $request->user()->profile?->id, 404);

        return view('pages.checkout.success', ['order' => $order->load('items', 'payments'), 'statusLabels' => OrderService::STATUS_LABELS]);
    }

    public function cancel(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->profile?->id, 404);
        $orders->transition($order, 'CANCELLED', true);

        return back()->with('status', 'Đã hủy đơn và hoàn lại tồn kho.');
    }

    public function reorder(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->profile?->id, 404);
        $orders->reorder($request->user(), $order);
        $request->session()->forget('checkout_token');

        return redirect()->route('cart.index')->with('status', 'Đã thêm lại các sản phẩm còn bán vào giỏ hàng.');
    }
}
