<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\MockData;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.home', array_replace(MockData::home(), [
            'categories' => Category::withCount(['products' => fn ($query) => $query->where('is_active', true)])->orderBy('id')->get(),
            'products' => Product::with('category')->where('is_active', true)->orderByDesc('id')->limit(4)->get(),
        ]));
    }
}
