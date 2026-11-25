<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class MaintenanceErrorPageTest extends TestCase
{
    public function test_guest_can_return_home_from_an_unknown_page(): void
    {
        $this->get('/trang-khong-ton-tai')->assertNotFound()
            ->assertSee('Trang này đang bảo trì')
            ->assertSee('href="'.route('home').'">Về trang chủ', false);

        $this->get(route('home'))->assertOk();
        $this->assertGuest();
    }

    public function test_csrf_error_does_not_sign_out_a_logged_in_account(): void
    {
        $account = Account::factory()->create(['username' => 'khach-an-tam']);
        $login = $this->post(route('login.submit'), ['username' => $account->username, 'password' => 'password']);
        $login->assertRedirect(route('home'));
        $sessionCookie = config('session.cookie');
        $this->withCookie($sessionCookie, $login->getCookie($sessionCookie)->getValue());
        $this->app['auth']->forgetGuards();

        // Enable CSRF verification for this request, which normally skips it in tests.
        $this->app['env'] = 'local';
        try {
            $this->post(route('logout'))->assertStatus(419)
                ->assertSee('Trang này đang bảo trì')
                ->assertSee('href="'.route('home').'">Về trang chủ', false);
        } finally {
            $this->app['env'] = 'testing';
        }

        $this->app['auth']->forgetGuards();
        $this->get(route('home'))->assertOk()->assertSee($account->username);
        $this->assertAuthenticatedAs($account);
    }

    public function test_server_errors_use_the_same_page_and_hide_exception_details(): void
    {
        config(['app.debug' => true]);
        Route::get('/kiem-thu-loi-may-chu', function (): never {
            throw new RuntimeException('Internal exception details');
        });

        $this->get('/kiem-thu-loi-may-chu')->assertInternalServerError()
            ->assertSee('Trang này đang bảo trì')
            ->assertDontSee('Internal exception details');
    }

    public function test_error_responses_keep_http_headers_and_json_payloads(): void
    {
        $this->delete(route('products.index'))->assertMethodNotAllowed()
            ->assertHeader('Allow', 'GET, HEAD')
            ->assertSee('Trang này đang bảo trì');

        $this->getJson('/trang-khong-ton-tai')->assertNotFound()
            ->assertJsonStructure(['message'])
            ->assertDontSee('Trang này đang bảo trì');
    }
}
