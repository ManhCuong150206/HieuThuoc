<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.customers', ['accounts' => Account::with('profile')->orderByDesc('id')->paginate(20)]);
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        abort_if(strtolower($account->role) === 'admin', 403);
        $account->is_active = (bool) $data['is_active'];
        $account->save();

        return back()->with('status', 'Đã cập nhật trạng thái tài khoản.');
    }
}
