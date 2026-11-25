<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Http\Requests\Checkout\SelectCheckoutItemsRequest;
use App\Models\Order;
use App\Models\Promotion;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function select(SelectCheckoutItemsRequest $request, CartService $cart): RedirectResponse
    {
        $data = $request->validated();
        $itemIds = array_map('intval', $data['items']);

        if (! $cart->containsItems($request->user(), $itemIds)) {
            return redirect()->route('cart.index')->withErrors(['items' => 'Sản phẩm đã chọn thay đổi. Vui lòng kiểm tra lại giỏ hàng.']);
        }

        $request->session()->put('checkout_item_ids', $itemIds);
        $request->session()->forget('checkout_token');

        return redirect()->route('checkout.index');
    }

    public function index(Request $request, CartService $cart): View|RedirectResponse
    {
        $profile = $request->user()->profile;
        if (! $profile) {
            return redirect()->route('account.index')->withErrors(['cart' => 'Vui lòng cập nhật thông tin cá nhân trước khi mua hàng.']);
        }
        $itemIds = $request->session()->get('checkout_item_ids', []);
        if ($itemIds === []) {
            return redirect()->route('cart.index')->withErrors(['items' => 'Vui lòng chọn ít nhất một sản phẩm để đặt hàng.']);
        }

        $items = $cart->itemsFor($request->user(), $itemIds);
        if ($items->count() !== count($itemIds)) {
            return redirect()->route('cart.index')->withErrors(['items' => 'Sản phẩm đã chọn thay đổi. Vui lòng kiểm tra lại giỏ hàng.']);
        }
        if (! $request->session()->has('checkout_token')) {
            $request->session()->put('checkout_token', Str::random(48));
        }

        $subtotal = $items->sum->subtotal();
        $productSavings = $items->sum->regularSubtotal() - $subtotal;
        $promotions = Promotion::availableAtCheckout()->where('applies_to_all_products', true)->orderBy('name')->get();
        $promotionDiscounts = $promotions->mapWithKeys(fn (Promotion $promotion) => [
            $promotion->id => $promotion->discountForSubtotal($subtotal),
        ]);

        return view('pages.checkout.index', [
            'profile' => $profile, 'items' => $items,
            'subtotal' => $subtotal,
            'productSavings' => $productSavings,
            'shippingFee' => OrderService::SHIPPING_FEE,
            'promotions' => $promotions,
            'promotionDiscounts' => $promotionDiscounts,
            'checkoutToken' => $request->session()->get('checkout_token'),
        ]);
    }

    public function store(PlaceOrderRequest $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validated();
        abort_unless(hash_equals((string) $request->session()->get('checkout_token', ''), $data['checkout_token']), 419);
        $itemIds = $request->session()->get('checkout_item_ids', []);
        abort_if($itemIds === [], 419);
        $order = $orders->place($request->user(), $data, $data['checkout_token'], $itemIds);
        $request->session()->put('last_order_id', $order->id);

        return redirect()->route('checkout.success', $order);
    }

    public function success(Request $request, ?Order $order = null): View|RedirectResponse
    {
        if (! $order) {
            $order = Order::find($request->session()->get('last_order_id'));
        }
        if (! $order) {
            return redirect()->route('account.orders');
        }
        abort_unless($order->belongsToAccount($request->user()), 404);

        return view('pages.checkout.success', ['order' => $order->load('items.product', 'payments'), 'statusLabels' => OrderService::STATUS_LABELS]);
    }
}
