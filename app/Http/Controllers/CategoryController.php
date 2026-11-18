<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __invoke(Request $request, Category $category): View
    {
        return app(ProductController::class)->index($request, $category);
    }
}
