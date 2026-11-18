<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, ?Category $category = null): View
    {
        $data = $request->validate(['sort' => ['nullable', Rule::in(['name', 'price_asc', 'price_desc'])]]);
        $sort = $data['sort'] ?? 'name';
        $products = Product::with('category')->where('is_active', true)
            ->when($category, fn ($query) => $query->where('category_id', $category->id));

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
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('pages.products.show', [
            'product' => $product,
            'category' => $product->category,
            'relatedProducts' => Product::with('category')->where('category_id', $product->category_id)
                ->where('is_active', true)->whereKeyNot($product->id)->orderBy('id')->limit(4)->get(),
        ]);
    }
}
