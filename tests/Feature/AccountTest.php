<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('username');
            $table->string('password');
            $table->string('role');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->unique()->constrained('accounts');
            $table->string('full_name', 150);
            $table->string('email', 254)->unique();
            $table->string('phone', 20);
            $table->string('address', 255)->nullable();
        });
        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->integer('quantity');
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('order_code');
            $table->string('status');
            $table->date('created_at');
            $table->integer('total_amount');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('shipping_address');
        });
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->string('product_name');
            $table->integer('unit_price');
            $table->integer('quantity');
        });

        DB::table('accounts')->insert([
            ['id' => 1, 'username' => 'customer1', 'password' => '123', 'role' => 'CUSTOMER'],
            ['id' => 2, 'username' => 'customer2', 'password' => '123', 'role' => 'CUSTOMER'],
            ['id' => 3, 'username' => 'legacy', 'password' => '123', 'role' => 'CUSTOMER'],
        ]);
        DB::table('users')->insert([
            ['id' => 11, 'account_id' => 1, 'full_name' => 'Nguyễn An', 'email' => 'an@example.com', 'phone' => '0912345678', 'address' => '12 Đường A'],
            ['id' => 12, 'account_id' => 2, 'full_name' => 'Khách khác', 'email' => 'other@example.com', 'phone' => '0987654321', 'address' => 'Địa chỉ riêng tư'],
        ]);
    }

    private function profileData(array $overrides = []): array
    {
        return array_replace([
            'full_name' => 'Nguyễn An cập nhật',
            'email' => 'new@example.com',
            'phone' => '0901234567',
            'address' => '34 Đường B',
        ], $overrides);
    }

    public function test_guests_cannot_view_or_update_a_profile(): void
    {
        $this->get('/tai-khoan')->assertRedirect(route('login'));
        $this->put('/tai-khoan', $this->profileData())->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['id' => 11, 'full_name' => 'Nguyễn An']);
    }

    public function test_profile_displays_real_fields_and_empty_order_history(): void
    {
        $this->actingAs(Account::find(1))->get('/tai-khoan')->assertOk()
            ->assertSee('Xin chào, Nguyễn An')->assertSee('an@example.com')->assertSee('12 Đường A')
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('Bạn chưa có đơn hàng nào.')
            ->assertDontSee('Minh Anh')->assertDontSee('other@example.com');
    }

    public function test_update_only_changes_the_authenticated_accounts_profile(): void
    {
        $data = $this->profileData(['id' => 12, 'account_id' => 2, 'role' => 'ADMIN', 'password' => 'hacked']);
        $this->actingAs(Account::find(1))->put('/tai-khoan', $data)
            ->assertRedirect(route('account.index').'#thong-tin-ca-nhan')->assertSessionHas('status');

        $this->assertDatabaseHas('users', ['id' => 11, 'account_id' => 1, ...$this->profileData()]);
        $this->assertDatabaseHas('users', ['id' => 12, 'email' => 'other@example.com', 'full_name' => 'Khách khác']);
        $this->assertDatabaseHas('accounts', ['id' => 1, 'role' => 'CUSTOMER', 'password' => '123']);
        $this->get('/tai-khoan')->assertSee('Nguyễn An cập nhật')->assertSee('34 Đường B');
    }

    public function test_own_email_is_allowed_but_duplicate_email_is_rejected(): void
    {
        $this->actingAs(Account::find(1))->put('/tai-khoan', $this->profileData(['email' => 'an@example.com']))
            ->assertSessionHasNoErrors();
        $this->put('/tai-khoan', $this->profileData(['email' => 'other@example.com']))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseHas('users', ['id' => 11, 'email' => 'an@example.com']);
    }

    public function test_invalid_fields_are_rejected_without_saving(): void
    {
        $this->actingAs(Account::find(1))->put('/tai-khoan', $this->profileData([
            'full_name' => '', 'email' => 'invalid', 'phone' => 'abc', 'address' => str_repeat('a', 256),
        ]))->assertSessionHasErrors(['full_name', 'email', 'phone', 'address']);
        $this->assertDatabaseHas('users', ['id' => 11, 'full_name' => 'Nguyễn An', 'address' => '12 Đường A']);
    }

    public function test_legacy_account_can_create_its_own_missing_profile(): void
    {
        $this->actingAs(Account::find(3))->get('/tai-khoan')->assertOk()
            ->assertSee('Xin chào, legacy')->assertDontSee('an@example.com');
        $this->put('/tai-khoan', $this->profileData(['address' => null]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['account_id' => 3, 'email' => 'new@example.com', 'address' => null]);
        $this->assertDatabaseCount('users', 3);
    }

    public function test_orders_and_order_items_are_limited_to_current_profile(): void
    {
        foreach ([[1, 11, 'MY-ORDER', 'PENDING'], [2, 12, 'PRIVATE-ORDER', 'SHIPPING']] as [$id, $userId, $code, $status]) {
            DB::table('orders')->insert([
                'id' => $id, 'user_id' => $userId, 'order_code' => $code, 'status' => $status,
                'created_at' => '2026-11-17', 'total_amount' => 20000, 'recipient_name' => 'Người nhận',
                'recipient_phone' => '0912345678', 'shipping_address' => 'Địa chỉ nhận hàng',
            ]);
            DB::table('order_items')->insert([
                'order_id' => $id, 'product_name' => $id === 1 ? 'Sản phẩm đã mua' : 'Món hàng riêng tư',
                'quantity' => 2, 'unit_price' => 10000,
            ]);
        }

        $this->actingAs(Account::find(1))->get('/tai-khoan')->assertOk()
            ->assertSee('MY-ORDER')->assertSee('17/11/2026')->assertSee('20.000đ')->assertSee('Chờ xác nhận')
            ->assertSee('Sản phẩm đã mua')->assertSee('2 sản phẩm')
            ->assertDontSee('PRIVATE-ORDER')->assertDontSee('Món hàng riêng tư');
    }

    public function test_header_logout_is_visible_next_to_the_cart(): void
    {
        $response = $this->actingAs(Account::find(1))->get('/');
        $response->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $xpath = new \DOMXPath($dom);
        $buttons = $xpath->query('//div[@class="header-actions"]/form/button[@class="header-logout"]');
        $this->assertCount(1, $buttons);
        $this->assertSame('Đăng xuất', $buttons[0]->textContent);
        $this->assertSame('header-action header-cart ', $xpath->query('../preceding-sibling::a[1]', $buttons[0])[0]->getAttribute('class'));
        $response->assertDontSee('header-account-dropdown');
    }
}
