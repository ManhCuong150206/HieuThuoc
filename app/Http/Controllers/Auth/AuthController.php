<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.auth.login');
    }

    public function showRegister(): View
    {
        return view('pages.auth.register');
    }

    public function register(RegisterRequest $request, AccountService $accounts): RedirectResponse
    {
        $data = $request->validated();
        $accounts->register($data);

        return redirect()->route('login')
            ->with('status', 'Đăng ký thành công! Bạn có thể đăng nhập bằng tài khoản vừa tạo.')
            ->withInput(['username' => $data['username']]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $account = Account::where('username', $credentials['username'])->first();
        if (! $account || ! $account->is_active || ! $account->matchesPassword($credentials['password'])) {
            throw ValidationException::withMessages([
                'username' => 'Tên đăng nhập hoặc mật khẩu không đúng, hoặc tài khoản đã bị khóa.',
            ]);
        }

        Auth::login($account);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route($account->isAdmin() ? 'admin.dashboard' : 'home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
