<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:accounts,username'],
            'email' => ['required', 'email', 'max:254', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9 .()-]{8,18}[0-9]$/'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ], [
            'required' => 'Vui lòng nhập :attribute.',
            'max' => ':attribute không được vượt quá :max ký tự.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email này đã được đăng ký.',
            'username.unique' => 'Tên đăng nhập này đã được sử dụng.',
            'username.regex' => 'Tên đăng nhập chỉ gồm chữ không dấu, số, dấu chấm, gạch dưới hoặc gạch ngang.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
            'password.min' => 'Mật khẩu cần ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
        ], [
            'full_name' => 'họ và tên',
            'username' => 'tên đăng nhập',
            'email' => 'email',
            'phone' => 'số điện thoại',
            'password' => 'mật khẩu',
            'password_confirmation' => 'mật khẩu xác nhận',
        ]);

        DB::transaction(function () use ($data): void {
            $account = new Account;
            $account->username = $data['username'];
            $account->password = Hash::make($data['password']);
            $account->role = 'CUSTOMER';
            $account->is_active = true;
            $account->created_at = now()->toDateString();
            $account->save();

            DB::table('users')->insert([
                'account_id' => $account->id,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);
        });

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
        $validPassword = false;

        if ($account) {
            // Support the existing database's plain passwords as well as password hashes.
            $validPassword = password_get_info($account->password)['algoName'] !== 'unknown'
                ? password_verify($credentials['password'], $account->password)
                : hash_equals($account->password, $credentials['password']);
        }

        if (! $account || ! $account->is_active || ! $validPassword) {
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
