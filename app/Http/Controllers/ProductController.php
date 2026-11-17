<?php

namespace App\Http\Controllers;

use App\Support\MockData;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('pages.products.index', MockData::catalog());
    }

    public function show(string $slug): View
    {
        $product = MockData::products()[$slug] ?? abort(404);

        return view('pages.products.show', [
            'product' => $product,
            'category' => MockData::categories()[$product['category']],
            'relatedProducts' => MockData::featured(),
            'information' => MockData::productInformation(),
        ]);
    }
}
