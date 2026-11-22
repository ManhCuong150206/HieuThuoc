<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function test_reset_link_uses_profile_email_and_token_is_single_use(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $this->get(route('password.request'))->assertOk();
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasNoErrors();
        $resetUrl = null;
        Mail::assertSent(PasswordResetMail::class, function ($mail) use ($user, &$resetUrl) {
            $resetUrl = $mail->resetUrl;

            return $mail->hasTo($user->email);
        });
        $token = basename(parse_url($resetUrl, PHP_URL_PATH));
        $reset = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotSame($token, $reset->token);
        $this->assertTrue(Hash::check($token, $reset->token));
        $this->get($resetUrl)->assertOk();
        $data = ['email' => $user->email, 'token' => $token, 'password' => 'newPassword123', 'password_confirmation' => 'newPassword123'];
        $this->post(route('password.update'), $data)->assertSessionHasNoErrors()->assertRedirect(route('login'));
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->assertTrue(Hash::check('newPassword123', $user->account->password));
        $this->post(route('password.update'), $data)->assertSessionHasErrors('email');
        $this->post(route('login.submit'), ['username' => $user->account->username, 'password' => 'newPassword123'])->assertRedirect(route('home'));
    }

    public function test_expired_or_invalid_token_does_not_change_password(): void
    {
        $user = User::factory()->create();
        $original = $user->account->password;
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => Hash::make(str_repeat('a', 64)), 'created_at' => now()->subHours(2)]);
        $data = ['email' => $user->email, 'token' => str_repeat('a', 64), 'password' => 'newPassword123', 'password_confirmation' => 'newPassword123'];
        $this->post(route('password.update'), $data)->assertSessionHasErrors('email');
        DB::table('password_reset_tokens')->where('email', $user->email)->update(['created_at' => now()]);
        $this->post(route('password.update'), array_replace($data, ['token' => str_repeat('b', 64)]))->assertSessionHasErrors('email');
        $this->assertSame($original, $user->account->fresh()->password);
    }

    public function test_unknown_email_is_not_disclosed_and_resend_is_throttled(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => 'unknown@example.test'])->assertSessionHas('status');
        Mail::assertNothingSent();
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasNoErrors();
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasNoErrors();
        Mail::assertSentCount(1);
    }
}
