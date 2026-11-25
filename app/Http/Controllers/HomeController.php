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
            ->whereHas('order', fn ($order) => $order->fulfilled())
            ->groupBy('product_id');

        return view('pages.home', [
            'promises' => [
                ['shield', 'Thông tin minh bạch', 'Nguồn gốc rõ ràng'],
                ['chat', 'Dược sĩ đồng hành', 'Giải đáp khi bạn cần'],
                ['truck', 'Giao hàng thuận tiện', 'Theo dõi từng đơn hàng'],
                ['heart', 'Chăm sóc tận tâm', 'Hỗ trợ trước và sau mua'],
            ],
            'categories' => Category::orderBy('id')->get(),
            'saleProducts' => Product::forCatalog()->onSale()
                ->orderByDesc('product_discount_percent')->orderByDesc('products.id')->limit(4)->get(),
            'bestSellers' => Product::select('products.*')->forCatalog()
                ->joinSub($sales, 'sales', 'sales.product_id', '=', 'products.id')
                ->orderByDesc('sales.units_sold')->orderBy('products.id')->limit(4)->get(),
            'articles' => Post::latestPublished()->limit(3)->get(),
        ]);
    }
}
