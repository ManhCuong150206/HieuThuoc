<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __invoke(Request $request, CartService $cart): View
    {
        $items = $cart->itemsFor($request->user());

        return view('pages.cart.index', [
            'items' => $items,
            'quantity' => (int) $items->sum('quantity'),
            'subtotal' => $items->sum(fn ($item) => $item->price * $item->quantity),
        ]);
    }
}
