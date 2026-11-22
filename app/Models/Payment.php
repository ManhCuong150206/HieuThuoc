<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected $fillable = ['order_id', 'method', 'amount', 'status', 'created_at', 'paid_at'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'created_at' => 'date', 'paid_at' => 'date'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
