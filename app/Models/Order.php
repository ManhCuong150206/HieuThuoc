<?php

namespace App\Models;

use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected $fillable = ['order_code', 'user_id', 'recipient_name', 'recipient_phone', 'shipping_address', 'subtotal', 'promotion_id', 'discount_amount', 'shipping_fee', 'total_amount', 'payment_method', 'status', 'created_at'];

    protected function casts(): array
    {
        return ['subtotal' => 'integer', 'discount_amount' => 'integer', 'shipping_fee' => 'integer', 'total_amount' => 'integer', 'created_at' => 'date'];
    }

    public function scopeFulfilled(Builder $query): Builder
    {
        return $query->whereIn('orders.status', ['DELIVERED', 'COMPLETED']);
    }

    public function belongsToAccount(Account $account): bool
    {
        return $this->user_id === $account->profile?->id;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(CustomerProfile::class, 'user_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
