<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Queries\ProductReviewQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(CatalogFilterRequest $request, ?Category $category = null): View
    {
        $data = $request->validated();
        $sort = $data['sort'] ?? 'name';
        $onSale = (bool) ($data['on_sale'] ?? false);
        $products = Product::forCatalog()
            ->when($onSale, fn (Builder $query) => $query->onSale())
            ->when($category, fn (Builder $query) => $query->where('category_id', $category->id))
            ->matchingCatalogSearch($data['q'] ?? null)
            ->orderedForCatalog($sort);

        return view('pages.products.index', [
            'category' => $category,
            'categories' => Category::orderBy('id')->get(),
            'products' => $products->paginate($category ? 12 : 6)->withQueryString(),
            'sort' => $sort,
            'search' => $data['q'] ?? '',
            'onSale' => $onSale,
        ]);
    }

    public function show(Request $request, Product $product, ProductReviewQuery $reviewQuery): View
    {
        abort_unless($product->is_active, 404);
        $product->product_discount_percent = Product::withDiscountPercent()->whereKey($product->id)
            ->value('product_discount_percent');
        $rating = filter_var($request->query('rating'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]) ?: null;
        $focus = filter_var($request->query('review'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $reviewSummary = $reviewQuery->summary($product, $rating, $focus);
        $reviewSummary['reviews']->appends(['rating' => $rating])->fragment('danh-gia');

        return view('pages.products.show', [
            'product' => $product,
            'category' => $product->category,
            ...$reviewSummary,
            'rating' => $rating,
            'relatedProducts' => Product::forCatalog()->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)->orderBy('id')->limit(4)->get(),
        ]);
    }
}
