<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __invoke(Request $request): View
    {
        $profile = $request->user()->profile;
        $orders = DB::table('orders')->where('user_id', $profile?->id)
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(10, ['*'], 'orders_page')
            ->fragment('don-hang');
        $orderItems = DB::table('order_items')->whereIn('order_id', $orders->pluck('id'))
            ->orderBy('id')->get()->groupBy('order_id');

        return view('pages.account.index', [
            'profile' => $profile,
            'account' => $request->user(),
            'orders' => $orders,
            'orderItems' => $orderItems,
            'statusLabels' => [
                'PENDING' => 'Chờ xác nhận',
                'CONFIRMED' => 'Đã xác nhận',
                'PROCESSING' => 'Đang xử lý',
                'SHIPPING' => 'Đang giao',
                'DELIVERED' => 'Đã giao',
                'COMPLETED' => 'Hoàn thành',
                'CANCELLED' => 'Đã hủy',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $account = $request->user();
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:254', Rule::unique('users', 'email')->ignore($account->profile?->id)],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9 .()-]{8,18}[0-9]$/'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'required' => 'Vui lòng nhập :attribute.',
            'max' => ':attribute không được vượt quá :max ký tự.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email này đã được tài khoản khác sử dụng.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
        ], [
            'full_name' => 'họ và tên',
            'email' => 'email',
            'phone' => 'số điện thoại',
            'address' => 'địa chỉ',
        ]);

        $profile = $account->profile()->updateOrCreate([], $data);
        $account->setRelation('profile', $profile);

        return redirect()->to(route('account.index').'#thong-tin-ca-nhan')
            ->with('status', 'Đã lưu thông tin cá nhân thành công.');
    }
}
