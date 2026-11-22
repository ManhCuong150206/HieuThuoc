<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, ?Category $category = null): View
    {
        $data = $request->validate(['sort' => ['nullable', Rule::in(['name', 'price_asc', 'price_desc'])], 'q' => ['nullable', 'string', 'max:100']]);
        $sort = $data['sort'] ?? 'name';
        $products = Product::with('category')->where('is_active', true)
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->when($data['q'] ?? null, fn ($query, $text) => $query->where(fn ($q) => $q->where('name', 'like', '%'.$text.'%')->orWhere('brand', 'like', '%'.$text.'%')));

        match ($sort) {
            'price_asc' => $products->orderBy('price'),
            'price_desc' => $products->orderByDesc('price'),
            default => $products->orderBy('name'),
        };

        return view('pages.products.index', [
            'category' => $category,
            'categories' => Category::orderBy('id')->get(),
            'products' => $products->orderBy('id')->paginate($category ? 12 : 6)->withQueryString(),
            'sort' => $sort,
            'search' => $data['q'] ?? '',
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('pages.products.show', [
            'product' => $product,
            'category' => $product->category,
            'reviews' => Review::with('orderItem.order.user')->where('status', 'VISIBLE')
                ->whereHas('orderItem', fn ($q) => $q->where('product_id', $product->id))
                ->orderByDesc('id')->paginate(10),
            'relatedProducts' => Product::with('category')->where('category_id', $product->category_id)
                ->where('is_active', true)->whereKeyNot($product->id)->orderBy('id')->limit(4)->get(),
        ]);
    }
}
