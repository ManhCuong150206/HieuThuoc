<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CustomerProfile;
use App\Models\Product;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    private function queryFor(Account $account): Builder
    {
        return DB::table('cart_items')->join('users', 'users.id', '=', 'cart_items.user_id')
            ->where('users.account_id', $account->id);
    }

    public function quantityFor(?Account $account): int
    {
        return $account ? (int) $this->queryFor($account)->sum('cart_items.quantity') : 0;
    }

    public function itemsFor(Account $account): Collection
    {
        return $this->queryFor($account)->join('products', 'products.id', '=', 'cart_items.product_id')
            ->select('cart_items.id', 'cart_items.product_id', 'cart_items.quantity', 'products.name',
                'products.price', 'products.brand', 'products.image_url', 'products.quantity as stock',
                'products.is_active', 'products.expiry_date')
            ->orderBy('cart_items.id')->get();
    }

    public function profile(Account $account, bool $lock = false): CustomerProfile
    {
        $profile = $account->profile()->when($lock, fn ($q) => $q->lockForUpdate())->first();
        if (! $profile) {
            throw ValidationException::withMessages(['cart' => 'Vui lòng cập nhật thông tin cá nhân trước khi mua hàng.']);
        }

        return $profile;
    }

    public function ensureAvailable(Product $product, int $quantity): void
    {
        if (! $product->is_active || ($product->expiry_date && $product->expiry_date->lt(today()))) {
            throw ValidationException::withMessages(['cart' => $product->name.' hiện không còn bán hoặc đã hết hạn.']);
        }
        if ($product->price <= 0) {
            throw ValidationException::withMessages(['cart' => $product->name.' chưa có giá bán. Vui lòng chọn sản phẩm khác.']);
        }
        if ($quantity > $product->quantity || $quantity > 999 || $quantity < 1) {
            throw ValidationException::withMessages(['cart' => $product->name.' chỉ còn '.$product->quantity.' sản phẩm; mỗi sản phẩm tối đa 999 đơn vị.']);
        }
    }

    public function add(Account $account, int $productId, int $quantity): void
    {
        DB::transaction(function () use ($account, $productId, $quantity): void {
            $profile = $this->profile($account, true);
            $product = Product::lockForUpdate()->findOrFail($productId);
            $item = $profile->cartItems()->where('product_id', $productId)->first();
            $total = ($item?->quantity ?? 0) + $quantity;
            $this->ensureAvailable($product, $total);
            $profile->cartItems()->updateOrCreate(['product_id' => $productId], ['quantity' => $total]);
        }, 3);
    }

    public function update(Account $account, int $itemId, int $quantity): void
    {
        DB::transaction(function () use ($account, $itemId, $quantity): void {
            $profile = $this->profile($account, true);
            $item = $profile->cartItems()->findOrFail($itemId);
            $product = Product::lockForUpdate()->findOrFail($item->product_id);
            $this->ensureAvailable($product, $quantity);
            $item->update(['quantity' => $quantity]);
        }, 3);
    }

    public function remove(Account $account, ?int $itemId = null): void
    {
        DB::transaction(function () use ($account, $itemId): void {
            $profile = $this->profile($account, true);
            if ($itemId !== null) {
                $profile->cartItems()->findOrFail($itemId)->delete();
            } else {
                $profile->cartItems()->delete();
            }
        });
    }
}
