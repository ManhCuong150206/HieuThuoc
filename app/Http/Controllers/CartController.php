<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartQuantityRequest;
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
            'selectedIds' => $request->session()->get('checkout_item_ids', []),
        ]);
    }

    public function store(CartQuantityRequest $request, Product $product, CartService $cart): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $itemId = $cart->add($request->user(), $product->id, (int) $data['quantity']);
        $request->session()->forget('checkout_token');

        if (! $request->expectsJson() && $request->boolean('buy_now')) {
            $request->session()->put('checkout_item_ids', [$itemId]);

            return redirect()->route('checkout.index');
        }

        return $this->mutationResponse($request, $cart, 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    public function update(CartQuantityRequest $request, int $item, CartService $cart): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $quantity = (int) $data['quantity'];
        $prices = $cart->update($request->user(), $item, $quantity);
        $request->session()->forget('checkout_token');

        return $this->mutationResponse($request, $cart, 'Đã cập nhật số lượng.', [
            'cart_item' => ['id' => $item, 'quantity' => $quantity, ...$prices],
        ]);
    }

    public function destroy(Request $request, int $item, CartService $cart): RedirectResponse|JsonResponse
    {
        $cart->remove($request->user(), $item);
        $request->session()->forget('checkout_token');

        return $this->mutationResponse($request, $cart, 'Đã xóa sản phẩm khỏi giỏ hàng.', ['removed_item_id' => $item]);
    }

    public function clear(Request $request, CartService $cart): RedirectResponse|JsonResponse
    {
        $cart->remove($request->user());
        $request->session()->forget('checkout_token');

        return $this->mutationResponse($request, $cart, 'Đã xóa giỏ hàng.', ['cart_cleared' => true]);
    }

    private function mutationResponse(Request $request, CartService $cart, string $message, array $data = []): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'cart_quantity' => $cart->quantityFor($request->user()),
                ...$data,
            ]);
        }

        return back()->with('status', $message);
    }
}
