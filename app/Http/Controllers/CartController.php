<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __invoke(Request $request, CartService $cart): View
    {
        $items = $cart->itemsFor($request->user());

        return view('pages.cart.index', [
            'items' => $items, 'quantity' => (int) $items->sum('quantity'),
            'subtotal' => $items->sum(fn ($item) => (int) round($item->price) * $item->quantity),
        ]);
    }

    public function store(Request $request, Product $product, CartService $cart): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:999']]);
        $cart->add($request->user(), $product->id, (int) $data['quantity']);
        $request->session()->forget('checkout_token');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã thêm sản phẩm vào giỏ hàng.',
                'cart_quantity' => $cart->quantityFor($request->user()),
            ]);
        }

        return $request->boolean('buy_now')
            ? redirect()->route('checkout.index')
            : back()->with('status', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    public function update(Request $request, int $item, CartService $cart): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:999']]);
        $cart->update($request->user(), $item, (int) $data['quantity']);
        $request->session()->forget('checkout_token');

        return back()->with('status', 'Đã cập nhật số lượng.');
    }

    public function destroy(Request $request, int $item, CartService $cart): RedirectResponse
    {
        $cart->remove($request->user(), $item);
        $request->session()->forget('checkout_token');

        return back()->with('status', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function clear(Request $request, CartService $cart): RedirectResponse
    {
        $cart->remove($request->user());
        $request->session()->forget('checkout_token');

        return back()->with('status', 'Đã xóa giỏ hàng.');
    }
}
