<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PharmacyDatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Run the actual migrations in SQLite memory, independently of legacy UI fixtures.
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
    }

    private function product(): Product
    {
        return Product::create([
            'category_id' => Category::create(['name' => 'Danh mục thử'])->id,
            'name' => 'Sản phẩm thử',
            'price' => 12000,
        ]);
    }

    private function order(User $user): Order
    {
        $order = new Order([
            'order_code' => 'TEST-001',
            'recipient_name' => $user->full_name,
            'recipient_phone' => $user->phone,
            'shipping_address' => 'Địa chỉ thử',
            'subtotal' => 24000,
            'total_amount' => 24000,
            'payment_method' => 'COD',
        ]);
        $user->orders()->save($order);

        return $order;
    }

    public function test_all_dump_tables_exist_and_users_are_profiles(): void
    {
        foreach (['accounts', 'users', 'categories', 'products', 'cart_items', 'promotions',
            'orders', 'order_items', 'payments', 'favorites', 'reviews', 'posts'] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        $this->assertSame(['id', 'account_id', 'full_name', 'email', 'phone', 'address'], Schema::getColumnListing('users'));
        $this->assertFalse(Schema::hasColumn('accounts', 'updated_at'));
        $this->assertFalse(Schema::hasColumn('accounts', 'remember_token'));

        $user = User::factory()->create();
        $this->assertSame($user->account_id, $user->account->id);
        $this->assertSame($user->id, $user->account->profile->id);
        $this->assertSame(now()->toDateString(), $user->account->created_at->toDateString());
        $this->assertArrayNotHasKey('password', $user->account->toArray());
    }

    public function test_registration_and_login_work_on_the_real_schema(): void
    {
        $data = [
            'full_name' => 'Khách thử',
            'username' => 'schema_test',
            'email' => 'schema@example.com',
            'phone' => '0912345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post('/dang-ky', $data)->assertSessionHasNoErrors()->assertRedirect(route('login'));
        $this->post('/dang-nhap', ['username' => $data['username'], 'password' => $data['password']])
            ->assertSessionHasNoErrors()->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->get('/tai-khoan')->assertOk()->assertSee($data['full_name']);
    }

    public function test_order_cart_payment_review_and_post_relations_use_dump_foreign_keys(): void
    {
        // Deliberately keep profile and account IDs different.
        Account::factory()->create();
        $user = User::factory()->create();
        $this->assertNotSame($user->id, $user->account_id);
        $product = $this->product();
        $cart = $user->cartItems()->create(['product_id' => $product->id, 'quantity' => 2]);
        $this->assertSame($user->id, $cart->user->id);
        $this->assertSame($product->id, $cart->product->id);
        $this->assertSame($cart->id, $product->cartItems->sole()->id);
        $this->assertSame($product->id, $product->category->products->sole()->id);

        $promotion = Promotion::create([
            'name' => 'Ưu đãi thử', 'discount_percent' => 10,
            'starts_at' => '2026-11-01', 'ends_at' => '2026-11-30',
        ]);
        $order = $this->order($user);
        $order->promotion()->associate($promotion)->save();
        $item = $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name,
            'unit_price' => 12000, 'quantity' => 2,
        ]);
        $payment = $order->payments()->create(['method' => 'COD', 'amount' => 24000, 'status' => 'PENDING']);
        $review = $item->review()->create(['rating' => 5, 'comment' => 'Thử', 'status' => 'PENDING']);
        $post = $user->account->posts()->create(['title' => 'Bài viết thử', 'content' => 'Nội dung thử', 'status' => 'DRAFT']);

        $this->assertSame($user->id, $order->user->id);
        $this->assertSame($order->id, $promotion->orders->sole()->id);
        $this->assertSame($order->id, $user->orders->sole()->id);
        $this->assertSame($promotion->id, $order->promotion->id);
        $this->assertSame($order->id, $item->order->id);
        $this->assertSame($product->id, $item->product->id);
        $this->assertSame($item->id, $product->orderItems->sole()->id);
        $this->assertSame($order->id, $payment->order->id);
        $this->assertSame($review->id, $item->review->id);
        $this->assertSame($item->id, $review->orderItem->id);
        $this->assertSame($user->account_id, $post->author->id);
        $this->assertSame(24000, $order->total_amount);
        $this->assertFalse($payment->isDirty());
        $this->assertSame(now()->toDateString(), $payment->fresh()->created_at->toDateString());
        $this->assertNull($post->published_at);
    }

    public function test_duplicate_cart_products_are_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $data = ['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1];
        CartItem::create($data);

        $this->expectException(QueryException::class);
        CartItem::create($data);
    }

    public function test_foreign_keys_reject_a_payment_for_an_unknown_order(): void
    {
        $this->expectException(QueryException::class);
        Payment::create(['order_id' => 999, 'method' => 'COD', 'amount' => 1000, 'status' => 'PENDING']);
    }

    public function test_reapplying_baseline_and_seeding_preserve_existing_records(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $before = [];
        foreach (['accounts', 'users', 'categories', 'products'] as $table) {
            $before[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }

        $migration = require database_path('migrations/2026_11_21_000000_create_pharmacy_tables.php');
        $migration->up();
        $this->seed(DatabaseSeeder::class);

        foreach ($before as $table => $records) {
            $this->assertSame($records, DB::table($table)->orderBy('id')->get()->toJson());
        }
        $this->assertSame($user->id, $user->account->profile->id);
        $this->assertSame($product->id, Product::first()->id);
    }
}
