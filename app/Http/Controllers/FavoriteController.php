<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->profile;

        return view('pages.favorites', [
            'products' => $profile ? $profile->favoriteProducts()->with('category')->where('is_active', true)
                ->orderByDesc('favorites.created_at')->orderBy('products.id')->paginate(12) : null,
        ]);
    }

    public function store(Request $request, Product $product, CartService $cart): RedirectResponse|JsonResponse
    {
        abort_unless($product->is_active, 404);
        DB::transaction(function () use ($request, $product, $cart): void {
            $profile = $cart->profile($request->user(), true);
            $profile->favoriteProducts()->syncWithoutDetaching([$product->id]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã lưu sản phẩm yêu thích.',
                'product_id' => $product->id,
                'saved' => true,
            ]);
        }

        return back()->with('status', 'Đã lưu sản phẩm yêu thích.');
    }

    public function destroy(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $cart->profile($request->user())->favoriteProducts()->detach($product->id);

        return back()->with('status', 'Đã bỏ lưu sản phẩm.');
    }
}
