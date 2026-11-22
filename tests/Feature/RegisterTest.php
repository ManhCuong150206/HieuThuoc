<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

    }

    private function registrationData(array $overrides = []): array
    {
        return array_replace([
            'full_name' => 'Nguyễn Văn An',
            'username' => 'khachhang1',
            'email' => 'an@example.com',
            'phone' => '0912345678',
            'password' => 'matkhau123',
            'password_confirmation' => 'matkhau123',
        ], $overrides);
    }

    public function test_get_registration_form_and_login_link_work(): void
    {
        $this->get('/dang-ky')->assertOk()
            ->assertSee('action="'.route('register.submit').'"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('name="_token"', false);
        $this->get('/dang-nhap')->assertSee('href="'.route('register').'"', false);
    }

    public function test_registration_saves_customer_and_profile_then_allows_login(): void
    {
        $data = $this->registrationData(['role' => 'ADMIN', 'is_active' => false]);
        $this->post('/dang-ky', $data)->assertRedirect(route('login'))->assertSessionHas('status');
        $this->assertGuest();

        $account = DB::table('accounts')->where('username', $data['username'])->first();
        $this->assertTrue(Hash::check($data['password'], $account->password));
        $this->assertSame('CUSTOMER', $account->role);
        $this->assertSame(1, $account->is_active);
        $this->assertDatabaseHas('users', [
            'account_id' => $account->id,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        $this->get('/dang-nhap')->assertSee('Đăng ký thành công!');
        $this->post('/dang-nhap', ['username' => $data['username'], 'password' => $data['password']])
            ->assertRedirect(route('home'));
        $this->get('/admin')->assertForbidden();
    }

    public function test_duplicate_username_and_email_are_rejected(): void
    {
        $this->post('/dang-ky', $this->registrationData())->assertSessionHasNoErrors();

        $this->from('/dang-ky')->post('/dang-ky', $this->registrationData())
            ->assertRedirect('/dang-ky')->assertSessionHasErrors(['username', 'email']);
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('users', 1);
        $this->get('/dang-ky')->assertSee('Tên đăng nhập này đã được sử dụng.')
            ->assertSee('Email này đã được đăng ký.')
            ->assertDontSee('value="matkhau123"', false);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->post('/dang-ky', $this->registrationData(['password_confirmation' => 'different']))
            ->assertSessionHasErrors('password');
        $this->assertDatabaseCount('accounts', 0);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_fields_and_short_password_are_rejected(): void
    {
        $this->post('/dang-ky', $this->registrationData([
            'username' => 'invalid username',
            'email' => 'invalid-email',
            'phone' => 'abc',
            'password' => '123',
            'password_confirmation' => '123',
        ]))->assertSessionHasErrors(['username', 'email', 'phone', 'password']);
        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_missing_fields_do_not_create_an_account(): void
    {
        $this->post('/dang-ky', [])->assertSessionHasErrors([
            'full_name', 'username', 'email', 'phone', 'password', 'password_confirmation',
        ]);
        $this->assertDatabaseCount('accounts', 0);
        $this->assertDatabaseCount('users', 0);
    }
}
