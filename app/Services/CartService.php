<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    private function queryFor(Account $account): Builder
    {
        return DB::table('cart_items')
            ->join('users', 'users.id', '=', 'cart_items.user_id')
            ->where('users.account_id', $account->id);
    }

    public function quantityFor(?Account $account): int
    {
        return $account ? (int) $this->queryFor($account)->sum('cart_items.quantity') : 0;
    }

    public function itemsFor(Account $account): Collection
    {
        return $this->queryFor($account)
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->select('cart_items.id', 'cart_items.quantity', 'products.name', 'products.price', 'products.brand', 'products.image_url')
            ->orderBy('cart_items.id')
            ->get();
    }
}
