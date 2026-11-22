<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
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

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(CustomerProfile::class, 'favorites', 'product_id', 'user_id')
            ->using(Favorite::class)->withPivot('created_at');
    }

    public function imageSrc(): string
    {
        $localPath = public_path('images/'.$this->name.'.png');
        if (! is_file($localPath) && filter_var($this->image_url, FILTER_VALIDATE_URL)
            && in_array(parse_url($this->image_url, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return $this->image_url;
        }

        return asset('images/'.rawurlencode($this->name.'.png'));
    }
}
