<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected $attributes = ['applies_to_all_products' => true];

    protected $fillable = ['name', 'description', 'discount_percent', 'starts_at', 'ends_at', 'is_active', 'applies_to_all_products'];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'integer', 'starts_at' => 'date', 'ends_at' => 'date',
            'is_active' => 'boolean', 'applies_to_all_products' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeAvailableAtCheckout(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereDate('starts_at', '<=', today())->whereDate('ends_at', '>=', today())
            ->whereBetween('discount_percent', [0, 100]);
    }

    public function scopeProductOffers(Builder $query): Builder
    {
        return $query->availableAtCheckout()
            ->where('promotions.applies_to_all_products', false)
            ->where('promotions.discount_percent', '>', 0)
            ->join('product_promotion', 'product_promotion.promotion_id', '=', 'promotions.id');
    }

    public static function productDiscountPercentQuery(): Builder
    {
        return static::productOffers()
            ->whereColumn('product_promotion.product_id', 'products.id')
            ->select('promotions.discount_percent')->orderByDesc('promotions.discount_percent')->limit(1);
    }

    public function discountForSubtotal(int $subtotal): int
    {
        return (int) floor($subtotal * $this->discount_percent / 100);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
