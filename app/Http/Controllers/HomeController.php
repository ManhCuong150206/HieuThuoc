<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.home', [
            'promises' => [
                ['shield', 'Thông tin minh bạch', 'Nguồn gốc rõ ràng'],
                ['chat', 'Dược sĩ đồng hành', 'Giải đáp khi bạn cần'],
                ['truck', 'Giao hàng thuận tiện', 'Theo dõi từng đơn hàng'],
                ['heart', 'Chăm sóc tận tâm', 'Hỗ trợ trước và sau mua'],
            ],
            'categories' => Category::withCount(['products' => fn ($query) => $query->where('is_active', true)])->orderBy('id')->get(),
            'products' => Product::with('category')->where('is_active', true)->orderByDesc('id')->limit(4)->get(),
            'articles' => Post::where('status', 'PUBLISHED')->whereDate('published_at', '<=', today())->orderByDesc('published_at')->limit(3)->get(),
        ]);
    }
}
