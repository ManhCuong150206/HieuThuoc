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
    public function test_otp_is_sent_to_profile_email_and_is_single_use(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $oldHash = $user->account->password;
        $this->get(route('password.request'))->assertOk();
        $this->get(route('password.reset'))->assertRedirect(route('password.request'));
        $this->post(route('password.email'), ['email' => $user->email])->assertRedirect(route('password.reset'));
        Mail::assertSent(PasswordResetMail::class, fn ($mail) => $mail->hasTo($user->email));
        $mail = Mail::sent(PasswordResetMail::class)->sole();
        $otp = $mail->otp;
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $otp);
        $this->assertStringContainsString($otp, $mail->render());
        $reset = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotSame($otp, $reset->token);
        $this->get(route('password.reset'))->assertOk()->assertSee($user->email)->assertSee('name="otp"', false)
            ->assertDontSee('name="password"', false);
        $data = ['password' => 'newPassword123', 'password_confirmation' => 'newPassword123'];
        $this->post(route('password.update'), $data + ['otp' => $otp])->assertRedirect(route('password.reset'));
        $this->post(route('password.verify'), ['otp' => $otp])->assertRedirect(route('password.reset'));
        $this->assertSame($oldHash, $user->account->fresh()->password);
        $this->get(route('password.reset'))->assertOk()->assertSee('name="password"', false)->assertDontSee('name="otp"', false);
        $this->post(route('password.update'), $data)->assertSessionHasNoErrors()->assertRedirect(route('login'))
            ->assertSessionHasInput('username', $user->account->username);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->assertTrue(Hash::check('newPassword123', $user->account->fresh()->password));
        $this->post(route('login.submit'), ['username' => $user->account->username, 'password' => 'password'])
            ->assertSessionHasErrors('username');
        $this->post(route('password.update'), $data)->assertRedirect(route('password.request'));
        $this->post(route('login.submit'), ['username' => $user->account->username, 'password' => 'newPassword123'])->assertRedirect(route('home'));
    }

    public function test_expired_or_locked_otp_does_not_change_password(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $original = $user->account->password;
        $this->post(route('password.email'), ['email' => $user->email]);
        $otp = Mail::sent(PasswordResetMail::class)->sole()->otp;
        $codes = DB::table('password_reset_tokens')->where('email', $user->email);
        $codes->update(['created_at' => now()->subMinutes(10)]);
        $this->post(route('password.verify'), ['otp' => $otp])->assertSessionHasErrors('otp');
        $codes->update(['created_at' => now()]);
        $wrongOtp = $otp === '000000' ? '000001' : '000000';
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('password.verify'), ['otp' => $wrongOtp])->assertSessionHasErrors('otp');
        }
        $this->assertSame(5, $codes->value('attempts'));
        $this->post(route('password.verify'), ['otp' => $otp])->assertSessionHasErrors('otp');
        $this->assertSame($original, $user->account->fresh()->password);
    }

    public function test_unknown_email_stays_on_the_email_step_and_resend_is_throttled(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $this->from(route('password.request'))->post(route('password.email'), ['email' => 'unknown@example.test'])
            ->assertRedirect(route('password.request'))->assertSessionHasErrors('email');
        Mail::assertNothingSent();
        $this->post(route('password.email'), ['email' => $user->email])->assertRedirect(route('password.reset'));
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasErrors('email');
        Mail::assertSentCount(1);
    }

    public function test_ajax_send_returns_an_inline_email_error_or_the_otp_form(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->postJson(route('password.email'), ['email' => 'unknown@example.test'])
            ->assertUnprocessable()->assertJsonPath('errors.email.0', 'Tài khoản không tồn tại.');
        Mail::assertNothingSent();

        $data = $this->postJson(route('password.email'), ['email' => $user->email])
            ->assertOk()->assertJsonPath('url', route('password.reset'))->json();
        $this->assertStringContainsString('name="otp"', $data['html']);
        $this->assertStringNotContainsString('name="password"', $data['html']);
        Mail::assertSentCount(1);
    }
}
