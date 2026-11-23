<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('categories')->insert([
            ['id' => 2, 'name' => 'Thuốc không kê đơn'],
            ['id' => 3, 'name' => 'Danh mục trống'],
        ]);
        DB::table('products')->insert([
            ['id' => 1, 'category_id' => 1, 'name' => 'Sản phẩm DB A', 'price' => 20000, 'is_active' => true, 'quantity' => 5, 'image_url' => 'images/test-a.png', 'composition' => 'Thành phần từ DB'],
            ['id' => 2, 'category_id' => 1, 'name' => 'Sản phẩm DB B', 'price' => 10000, 'is_active' => true, 'quantity' => 0, 'image_url' => null, 'composition' => null],
            ['id' => 3, 'category_id' => 2, 'name' => 'Sản phẩm danh mục khác', 'price' => 30000, 'is_active' => true, 'quantity' => 2, 'image_url' => null, 'composition' => null],
            ['id' => 4, 'category_id' => 1, 'name' => 'Sản phẩm ngừng bán', 'price' => 15000, 'is_active' => false, 'quantity' => 1, 'image_url' => null, 'composition' => null],
        ]);
    }

    public function test_header_and_home_use_database_categories(): void
    {
        $this->get('/')->assertOk()->assertSee('aria-controls="header-category-list"', false)
            ->assertSee('Thuốc không kê đơn')->assertSee('Danh mục trống')
            ->assertSee('href="'.route('categories.show', 2).'"', false)
            ->assertSee('Chưa có sản phẩm bán chạy.')->assertDontSee('Sản phẩm ngừng bán');

        DB::table('categories')->insert(['id' => 9, 'name' => 'Danh mục mới thêm trong DB']);
        $this->get('/san-pham')->assertSee('Danh mục mới thêm trong DB');
    }

    public function test_home_ranks_active_products_by_units_in_fulfilled_orders(): void
    {
        $user = User::factory()->create();
        $orders = [
            'DELIVERED' => [1 => 2, 2 => 4, 4 => 20],
            'COMPLETED' => [1 => 3],
            'CANCELLED' => [3 => 10],
        ];
        foreach ($orders as $status => $quantities) {
            $order = $user->orders()->create([
                'order_code' => $status, 'status' => $status, 'recipient_name' => $user->full_name,
                'recipient_phone' => $user->phone, 'shipping_address' => $user->address,
                'subtotal' => 10000, 'total_amount' => 10000, 'payment_method' => 'COD',
            ]);
            foreach ($quantities as $productId => $quantity) {
                $order->items()->create([
                    'product_id' => $productId, 'product_name' => 'Sản phẩm thử',
                    'unit_price' => 1000, 'quantity' => $quantity,
                ]);
            }
        }

        $this->get('/')->assertOk()->assertSeeInOrder(['Sản phẩm bán chạy', 'Sản phẩm DB A', 'Sản phẩm DB B'])
            ->assertViewHas('bestSellers', fn ($products) => $products->pluck('id')->all() === [1, 2]);
    }

    public function test_category_only_displays_its_available_database_products(): void
    {
        $response = $this->get('/danh-muc/1')->assertOk()->assertViewHas('category', fn ($category) => $category->id === 1);
        $response->assertSee('Sản phẩm DB A')->assertSee('Sản phẩm DB B')->assertSee('2 sản phẩm')
            ->assertDontSee('Sản phẩm danh mục khác')->assertDontSee('Sản phẩm ngừng bán')
            ->assertSee('20.000đ')->assertSee('http://localhost:8000/images/'.rawurlencode('Sản phẩm DB A.png'))
            ->assertDontSee('images/test-a.png')
            ->assertDontSee('sản phẩm minh họa');
        $response->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [1, 2]);
    }

    public function test_all_products_and_sorting_use_database_values(): void
    {
        $this->get('/san-pham?sort=price_asc')->assertOk()
            ->assertViewHas('category', null)
            ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [2, 1, 3]);
        $this->get('/danh-muc/1?sort=price_desc')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [1, 2]);
    }

    public function test_product_details_and_related_products_are_from_same_category(): void
    {
        $this->get('/san-pham/1')->assertOk()->assertSee('Thành phần từ DB')->assertSee('Số lượng còn: 5')
            ->assertSee('Sản phẩm DB B')->assertDontSee('Sản phẩm danh mục khác')
            ->assertDontSee('Sản phẩm ngừng bán')->assertDontSee('24 đánh giá');
        $this->get('/san-pham/4')->assertNotFound();
        $this->get('/san-pham/999')->assertNotFound();
        $this->get('/danh-muc/999')->assertNotFound();
    }

    public function test_empty_category_has_no_mock_products(): void
    {
        $this->get('/danh-muc/3')->assertOk()->assertSee('Chưa có sản phẩm trong danh mục này.')
            ->assertSee('0 sản phẩm')->assertDontSee('Sản phẩm DB A')->assertDontSee('Vitamin C 500 mg');
    }

    public function test_pagination_keeps_category_and_sort_and_exposes_every_product(): void
    {
        foreach (range(5, 19) as $id) {
            DB::table('products')->insert(['id' => $id, 'category_id' => 1, 'name' => 'Sản phẩm '.$id, 'price' => $id * 1000]);
        }

        $first = $this->get('/danh-muc/1?sort=price_asc')->assertOk();
        $first->assertViewHas('products', fn ($products) => $products->total() === 17 && $products->count() === 12 && str_contains($products->nextPageUrl(), 'sort=price_asc'));
        $second = $this->get('/danh-muc/1?sort=price_asc&page=2')->assertOk();
        $second->assertViewHas('products', fn ($products) => $products->count() === 5 && $products->every(fn ($product) => $product->category_id === 1));
    }

    public function test_all_products_shows_six_per_page_without_losing_products(): void
    {
        foreach (range(5, 19) as $id) {
            DB::table('products')->insert(['id' => $id, 'category_id' => 1, 'name' => 'Sản phẩm '.$id, 'price' => $id * 1000]);
        }

        $ids = [];
        foreach (range(1, 3) as $page) {
            $response = $this->get('/san-pham?sort=price_asc&page='.$page)->assertOk();
            $products = $response->viewData('products');
            $this->assertSame(6, $products->count());
            $this->assertSame(18, $products->total());
            $ids = array_merge($ids, $products->pluck('id')->all());
            if ($page < 3) {
                $this->assertStringContainsString('sort=price_asc', $products->nextPageUrl());
            }
        }
        $this->assertCount(18, array_unique($ids));
        $this->assertNotContains(4, $ids);
    }
}
