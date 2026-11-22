<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerProfile extends Model
{
    protected $table = 'users';

    public $timestamps = false;

    protected $fillable = ['full_name', 'email', 'phone', 'address'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites', 'user_id', 'product_id')
            ->using(Favorite::class)->withPivot('created_at');
    }
}
