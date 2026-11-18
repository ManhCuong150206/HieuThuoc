<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id');
        });
        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('product_id');
            $table->integer('quantity');
        });

        DB::table('accounts')->insert([
            ['username' => 'acc1', 'password' => '123', 'role' => 'CUSTOMER', 'is_active' => true],
            ['username' => 'admin', 'password' => 'admin', 'role' => 'admin', 'is_active' => true],
        ]);
    }

    public function test_login_form_submits_credentials(): void
    {
        $this->get('/dang-nhap')->assertOk()
            ->assertSee('name="username"', false)
            ->assertSee('name="password"', false)
            ->assertSee('type="submit"', false);
    }

    public function test_customer_goes_home_and_cannot_open_admin(): void
    {
        $this->withSession(['url.intended' => url('/admin')])
            ->post('/dang-nhap', ['username' => 'acc1', 'password' => '123'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs(Account::where('username', 'acc1')->first());
        $this->get('/')->assertOk()->assertSee('acc1');
        $this->get('/admin')->assertForbidden();
    }

    public function test_admin_goes_to_dashboard_and_session_persists(): void
    {
        $this->post('/dang-nhap', ['username' => 'admin', 'password' => 'admin'])
            ->assertRedirect(route('admin.dashboard'));

        // Force the next request to reload the account from its session identifier.
        $this->app['auth']->forgetGuards();
        $this->get('/admin')->assertOk()->assertSee('Tổng quan nhà thuốc');
    }

    public function test_wrong_password_and_unknown_account_are_rejected(): void
    {
        foreach (['admin', 'unknown'] as $username) {
            $this->from('/dang-nhap')
                ->post('/dang-nhap', ['username' => $username, 'password' => 'wrong'])
                ->assertRedirect('/dang-nhap')->assertSessionHasErrors('username');
            $this->assertGuest();
        }
    }

    public function test_inactive_account_is_rejected(): void
    {
        DB::table('accounts')->where('username', 'admin')->update(['is_active' => false]);

        $this->post('/dang-nhap', ['username' => 'admin', 'password' => 'admin'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_hashed_password_is_supported(): void
    {
        DB::table('accounts')->where('username', 'acc1')->update(['password' => Hash::make('123')]);

        $this->post('/dang-nhap', ['username' => 'acc1', 'password' => '123'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_guest_is_sent_to_login_when_opening_admin(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_logout_removes_admin_access(): void
    {
        $this->actingAs(Account::where('username', 'admin')->first())
            ->post('/dang-xuat')->assertRedirect(route('login'));

        $this->assertGuest();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_missing_credentials_are_rejected(): void
    {
        $this->post('/dang-nhap', [])->assertSessionHasErrors(['username', 'password']);
        $this->assertGuest();
    }
}
