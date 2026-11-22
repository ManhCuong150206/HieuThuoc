<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tài khoản đã bị khóa. Vui lòng đăng nhập lại.'], 401);
            }

            return redirect()->route('login')->withErrors(['username' => 'Tài khoản đã bị khóa.']);
        }

        return $next($request);
    }
}
