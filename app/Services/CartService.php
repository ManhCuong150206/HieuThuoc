<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CartItem;
use App\Models\CustomerProfile;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    private function queryFor(Account $account): Builder
    {
        return CartItem::where('user_id', $account->profile()->select('users.id')->toBase());
    }

    public function quantityFor(?Account $account): int
    {
        return $account ? (int) $this->queryFor($account)->sum('quantity') : 0;
    }

    /** @return Collection<int, CartItem> */
    public function itemsFor(Account $account, ?array $itemIds = null): Collection
    {
        return $this->queryFor($account)
            ->when($itemIds !== null, fn (Builder $query) => $query->whereKey($itemIds))
            ->with(['product' => fn ($product) => $product->withDiscountPercent()])
            ->orderBy('id')->get()->filter(fn (CartItem $item) => $item->product !== null);
    }

    public function containsItems(Account $account, array $itemIds): bool
    {
        return $this->queryFor($account)->whereKey($itemIds)->whereHas('product')->count() === count($itemIds);
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
        if (! $product->isAvailableForSale()) {
            throw ValidationException::withMessages(['cart' => $product->name.' hiện không còn bán hoặc đã hết hạn.']);
        }
        if ($product->price <= 0) {
            throw ValidationException::withMessages(['cart' => $product->name.' chưa có giá bán. Vui lòng chọn sản phẩm khác.']);
        }
        if ($quantity > $product->maxCartQuantity() || $quantity < 1) {
            throw ValidationException::withMessages(['cart' => $product->name.' chỉ còn '.$product->quantity.' sản phẩm; mỗi sản phẩm tối đa 999 đơn vị.']);
        }
    }

    public function add(Account $account, int $productId, int $quantity): int
    {
        return DB::transaction(function () use ($account, $productId, $quantity): int {
            $profile = $this->profile($account, true);
            $product = Product::lockForUpdate()->findOrFail($productId);
            $item = $profile->cartItems()->where('product_id', $productId)->first();
            $total = ($item?->quantity ?? 0) + $quantity;
            $this->ensureAvailable($product, $total);

            return $profile->cartItems()->updateOrCreate(['product_id' => $productId], ['quantity' => $total])->id;
        }, 3);
    }

    public function update(Account $account, int $itemId, int $quantity): array
    {
        return DB::transaction(function () use ($account, $itemId, $quantity): array {
            $profile = $this->profile($account, true);
            $item = $profile->cartItems()->findOrFail($itemId);
            $product = Product::withDiscountPercent()->lockForUpdate()->findOrFail($item->product_id);
            $this->ensureAvailable($product, $quantity);
            $item->update(['quantity' => $quantity]);

            return [
                'unit_price' => $product->saleUnitPrice(),
                'regular_unit_price' => $product->regularUnitPrice(),
            ];
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
