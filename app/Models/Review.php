<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    private const PUBLIC_STATUSES = ['VISIBLE', 'PENDING'];

    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected $fillable = ['order_item_id', 'rating', 'comment', 'status', 'created_at'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'created_at' => 'date'];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->whereIn('status', self::PUBLIC_STATUSES)->whereBetween('rating', [1, 5]);
    }

    public function isPubliclyVisible(): bool
    {
        return in_array($this->status, self::PUBLIC_STATUSES, true) && $this->rating >= 1 && $this->rating <= 5;
    }
}
