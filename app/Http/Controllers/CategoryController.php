<?php

namespace App\Http\Controllers;

use App\Support\MockData;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __invoke(string $slug): View
    {
        $category = MockData::categories()[$slug] ?? abort(404);

        return view('pages.products.index', MockData::catalog($category));
    }
}
