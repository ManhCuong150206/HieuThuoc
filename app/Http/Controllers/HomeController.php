<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $sales = OrderItem::selectRaw('product_id, SUM(quantity) AS units_sold')
            ->whereHas('order', fn ($order) => $order->whereIn('status', ['DELIVERED', 'COMPLETED']))
            ->groupBy('product_id');

        return view('pages.home', [
            'promises' => [
                ['shield', 'Thông tin minh bạch', 'Nguồn gốc rõ ràng'],
                ['chat', 'Dược sĩ đồng hành', 'Giải đáp khi bạn cần'],
                ['truck', 'Giao hàng thuận tiện', 'Theo dõi từng đơn hàng'],
                ['heart', 'Chăm sóc tận tâm', 'Hỗ trợ trước và sau mua'],
            ],
            'categories' => Category::orderBy('id')->get(),
            'bestSellers' => Product::with('category')->select('products.*')
                ->joinSub($sales, 'sales', 'sales.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->orderByDesc('sales.units_sold')->orderBy('products.id')->limit(4)->get(),
            'articles' => Post::where('status', 'PUBLISHED')->whereDate('published_at', '<=', today())->orderByDesc('published_at')->limit(3)->get(),
        ]);
    }
}
