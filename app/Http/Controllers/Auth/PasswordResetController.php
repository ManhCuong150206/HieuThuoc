<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(private PasswordResetService $passwords) {}

    public function requestForm(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:254']]);
        $email = $this->passwords->sendCode($data['email']);

        $request->session()->forget('password_reset_grant');
        $request->session()->put('password_reset_email', $email);

        $message = 'Đã gửi mã OTP. Mã có hiệu lực '.PasswordResetService::OTP_MINUTES.' phút.';
        if ($request->expectsJson()) {
            return response()->json([
                'url' => route('password.reset'),
                'html' => view('pages.auth.otp-form', ['email' => $email, 'message' => $message])->render(),
            ]);
        }

        return redirect()->route('password.reset')->with('status', $message);
    }

    public function resetForm(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        if (! $email) {
            return redirect()->route('password.request');
        }

        return view('pages.auth.reset-password', [
            'email' => $email,
            'verified' => $request->session()->has('password_reset_grant'),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        if (! $email) {
            return redirect()->route('password.request');
        }
        if ($request->session()->has('password_reset_grant')) {
            return redirect()->route('password.reset');
        }

        $data = $request->validate([
            'otp' => ['required', 'string', 'regex:/\A[0-9]{6}\z/'],
        ], ['otp.regex' => 'Mã OTP phải có đúng 6 chữ số.']);
        $grant = $this->passwords->verifyCode($email, $data['otp']);

        $request->session()->regenerate();
        $request->session()->put('password_reset_grant', $grant);

        return redirect()->route('password.reset')->with('status', 'Đã xác minh mã OTP.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        $grant = $request->session()->get('password_reset_grant');
        if (! $email || ! $grant) {
            return redirect()->route($email ? 'password.reset' : 'password.request');
        }

        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'max:72', 'confirmed']]);
        $username = $this->passwords->reset($email, $grant, $data['password']);
        $request->session()->forget(['password_reset_email', 'password_reset_grant']);
        if ($username === null) {
            return redirect()->route('password.request')->withErrors(['email' => 'Phiên đặt lại mật khẩu đã hết hiệu lực. Vui lòng yêu cầu mã mới.']);
        }

        return redirect()->route('login')
            ->with('status', 'Đã đặt lại mật khẩu. Vui lòng đăng nhập.')
            ->withInput(['username' => $username]);
    }
}
