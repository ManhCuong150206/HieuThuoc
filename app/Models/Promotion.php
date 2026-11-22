<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected $fillable = ['name', 'description', 'discount_percent', 'starts_at', 'ends_at', 'is_active'];

    protected function casts(): array
    {
        return ['discount_percent' => 'integer', 'starts_at' => 'date', 'ends_at' => 'date', 'is_active' => 'boolean'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
