<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('pages.account.index', [
            'account' => $request->user(),
            'profile' => $request->user()->profile,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $account = $request->user();
        $profile = $account->profile()->updateOrCreate([], $request->validated());
        $account->setRelation('profile', $profile);

        return redirect()->route('account.index')
            ->with('status', 'Đã lưu thông tin cá nhân thành công.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);
        $account = $request->user();
        if (! $account->matchesPassword($data['current_password'])) {
            throw ValidationException::withMessages(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }
        $account->changePassword($data['password']);
        $request->session()->regenerate();

        return back()->with('status', 'Đã đổi mật khẩu.');
    }
}
