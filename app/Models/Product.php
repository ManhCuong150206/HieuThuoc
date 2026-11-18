<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'quantity' => 'integer', 'is_active' => 'boolean', 'expiry_date' => 'date'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function imageSrc(): string
    {
        return asset('images/'.rawurlencode($this->name.'.png'));
    }
}
