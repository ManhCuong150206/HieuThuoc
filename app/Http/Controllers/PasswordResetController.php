<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\CustomerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:254']]);
        $profile = CustomerProfile::where('email', $data['email'])->first();
        if ($profile && $profile->account?->is_active) {
            $recent = DB::table('password_reset_tokens')->where('email', $profile->email)->first();
            if (! $recent || Carbon::parse($recent->created_at)->lte(now()->subMinute())) {
                $token = Str::random(64);
                DB::table('password_reset_tokens')->updateOrInsert(['email' => $profile->email], [
                    'token' => Hash::make($token), 'created_at' => now(),
                ]);
                $url = route('password.reset', ['token' => $token, 'email' => $profile->email]);
                Mail::to($profile->email)->send(new PasswordResetMail($url));
            }
        }

        return back()->with('status', 'Nếu email khớp tài khoản đang hoạt động, liên kết đặt lại mật khẩu sẽ được gửi. Liên kết có hiệu lực 60 phút.');
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('pages.auth.reset-password', ['token' => $token, 'email' => $request->query('email', '')]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:254'], 'token' => ['required', 'string', 'size:64'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);
        DB::transaction(function () use ($data): void {
            $reset = DB::table('password_reset_tokens')->where('email', $data['email'])->lockForUpdate()->first();
            $profile = CustomerProfile::where('email', $data['email'])->first();
            if (! $reset || ! $reset->created_at || Carbon::parse($reset->created_at)->lt(now()->subHour())
                || ! Hash::check($data['token'], $reset->token) || ! $profile?->account?->is_active) {
                throw ValidationException::withMessages(['email' => 'Liên kết không hợp lệ hoặc đã hết hạn.']);
            }
            $account = $profile->account()->lockForUpdate()->firstOrFail();
            $account->password = Hash::make($data['password']);
            $account->save();
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
        });

        return redirect()->route('login')->with('status', 'Đã đặt lại mật khẩu. Vui lòng đăng nhập.');
    }
}
