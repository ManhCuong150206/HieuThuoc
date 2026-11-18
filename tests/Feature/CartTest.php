<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('username');
            $table->string('password');
            $table->string('role');
            $table->boolean('is_active');
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->unique()->constrained('accounts');
        });
        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->unique(['user_id', 'product_id']);
        });

        DB::table('accounts')->insert([
            ['id' => 1, 'username' => 'customer1', 'password' => '123', 'role' => 'CUSTOMER', 'is_active' => true],
            ['id' => 2, 'username' => 'customer2', 'password' => '123', 'role' => 'CUSTOMER', 'is_active' => true],
            ['id' => 3, 'username' => 'admin', 'password' => 'admin', 'role' => 'ADMIN', 'is_active' => true],
        ]);
        // Profile IDs deliberately differ from account IDs.
        DB::table('users')->insert([
            ['id' => 25, 'account_id' => 1],
            ['id' => 26, 'account_id' => 2],
        ]);
        DB::table('products')->insert([
            ['id' => 10, 'name' => 'Sản phẩm DB A', 'price' => 10000],
            ['id' => 11, 'name' => 'Sản phẩm DB B', 'price' => 25000],
            ['id' => 12, 'name' => 'Sản phẩm riêng khách khác', 'price' => 99000],
        ]);
    }

    private function fillCarts(): void
    {
        DB::table('cart_items')->insert([
            ['user_id' => 25, 'product_id' => 10, 'quantity' => 3],
            ['user_id' => 25, 'product_id' => 11, 'quantity' => 2],
            ['user_id' => 26, 'product_id' => 12, 'quantity' => 9],
        ]);
    }

    public function test_guest_sees_zero_and_must_login_to_view_cart(): void
    {
        $this->fillCarts();
        $this->get('/')->assertOk()->assertSee('aria-label="Giỏ hàng, 0 sản phẩm"', false);
        $this->get('/gio-hang')->assertRedirect(route('login'));
    }

    public function test_empty_cart_does_not_show_sample_products(): void
    {
        $this->actingAs(Account::find(1))->get('/gio-hang')->assertOk()
            ->assertSee('Giỏ hàng của bạn đang trống')
            ->assertSee('aria-label="Giỏ hàng, 0 sản phẩm"', false)
            ->assertDontSee('Vitamin C 500 mg');
    }

    public function test_cart_counts_quantities_and_only_shows_current_customers_data(): void
    {
        $this->fillCarts();
        $this->actingAs(Account::find(1))->get('/gio-hang')->assertOk()
            ->assertSee('aria-label="Giỏ hàng, 5 sản phẩm"', false)
            ->assertSee('Sản phẩm DB A')->assertSee('Sản phẩm DB B')
            ->assertSee('Số lượng: 3')->assertSee('80.000đ')
            ->assertDontSee('Sản phẩm riêng khách khác');

        $this->actingAs(Account::find(2))->get('/gio-hang')->assertOk()
            ->assertSee('aria-label="Giỏ hàng, 9 sản phẩm"', false)
            ->assertSee('Sản phẩm riêng khách khác')->assertDontSee('Sản phẩm DB A');
    }

    public function test_account_without_customer_profile_has_an_empty_cart(): void
    {
        $this->fillCarts();
        $this->actingAs(Account::find(3))->get('/gio-hang')->assertOk()
            ->assertSee('Giỏ hàng của bạn đang trống')
            ->assertSee('aria-label="Giỏ hàng, 0 sản phẩm"', false);
    }

    public function test_header_reflects_database_changes_between_requests(): void
    {
        $this->fillCarts();
        $this->actingAs(Account::find(1))->get('/')->assertSee('aria-label="Giỏ hàng, 5 sản phẩm"', false);
        DB::table('cart_items')->where('user_id', 25)->where('product_id', 10)->update(['quantity' => 7]);
        $this->get('/')->assertSee('aria-label="Giỏ hàng, 9 sản phẩm"', false);
    }

    public function test_logout_resets_header_count_without_deleting_saved_cart(): void
    {
        $this->fillCarts();
        $this->actingAs(Account::find(1))->post('/dang-xuat')->assertRedirect(route('login'));
        $this->get('/')->assertSee('aria-label="Giỏ hàng, 0 sản phẩm"', false);
        $this->assertDatabaseCount('cart_items', 3);
    }
}
