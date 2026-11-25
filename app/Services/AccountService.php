<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountService
{
    public function register(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $account = new Account;
            $account->username = $data['username'];
            $account->role = 'CUSTOMER';
            $account->is_active = true;
            $account->created_at = today();
            $account->changePassword($data['password']);

            $account->profile()->create([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);
        });
    }
}
