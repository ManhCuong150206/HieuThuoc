<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public const MAX_CART_QUANTITY = 999;

    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected $fillable = [
        'category_id', 'name', 'price', 'brand', 'composition', 'used_for',
        'how_to_use', 'contraindications', 'side_effects', 'image_url',
        'quantity', 'expiry_date', 'is_active',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'quantity' => 'integer', 'is_active' => 'boolean', 'expiry_date' => 'date'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('products.is_active', true);
    }

    public function scopeForCatalog(Builder $query): Builder
    {
        return $query->with('category')->withDiscountPercent()->active();
    }

    public function scopeMatchingCatalogSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search !== null, function (Builder $query) use ($search): void {
            $pattern = '%'.$search.'%';
            $query->where(fn (Builder $query) => $query->where('name', 'like', $pattern)->orWhere('brand', 'like', $pattern));
        });
    }

    public function scopeOrderedForCatalog(Builder $query, string $sort): Builder
    {
        match ($sort) {
            'price_asc' => $query->orderByRaw('round(products.price) * (100 - coalesce(product_discount_percent, 0)) asc'),
            'price_desc' => $query->orderByRaw('round(products.price) * (100 - coalesce(product_discount_percent, 0)) desc'),
            default => $query->orderBy('name'),
        };

        return $query->orderBy('products.id');
    }

    public function isAvailableForSale(): bool
    {
        return $this->is_active && (! $this->expiry_date || $this->expiry_date->gte(today()));
    }

    public function maxCartQuantity(): int
    {
        return min($this->quantity, self::MAX_CART_QUANTITY);
    }

    public function canPurchase(int $quantity = 1): bool
    {
        return $this->isAvailableForSale() && $this->price > 0
            && $quantity >= 1 && $quantity <= $this->maxCartQuantity();
    }

    public function scopeWithDiscountPercent(Builder $query): Builder
    {
        return $query->addSelect(['product_discount_percent' => Promotion::productDiscountPercentQuery()]);
    }

    public function scopeOnSale(Builder $query): Builder
    {
        return $query->whereExists(Promotion::productDiscountPercentQuery()
            ->whereRaw('round(products.price) * promotions.discount_percent >= 100')
            ->toBase());
    }

    public static function discountedUnitPrice(int $regularPrice, int $percent): int
    {
        return $regularPrice - intdiv($regularPrice * $percent, 100);
    }

    public function regularUnitPrice(): int
    {
        return (int) round($this->price);
    }

    public function saleUnitPrice(): int
    {
        return self::discountedUnitPrice($this->regularUnitPrice(), (int) $this->product_discount_percent);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function imageSrc(): string
    {
        if ($this->hasUploadedImage()) {
            return asset($this->image_url);
        }

        $localPath = public_path('images/'.$this->name.'.png');
        if (! is_file($localPath) && filter_var($this->image_url, FILTER_VALIDATE_URL)
            && in_array(parse_url($this->image_url, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return $this->image_url;
        }

        return asset('images/'.rawurlencode($this->name.'.png'));
    }

    public function hasUploadedImage(): bool
    {
        return preg_match('~\Aimages/product-[a-zA-Z0-9]+\.(?:jpe?g|png|webp)\z~', $this->image_url ?? '') === 1;
    }
}
