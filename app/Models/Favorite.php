<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

// Composite key (user_id, product_id): manage entries through the favoriteProducts relation.
class Favorite extends Pivot
{
    protected $table = 'favorites';

    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected $fillable = ['user_id', 'product_id', 'created_at'];

    public function getUpdatedAtColumn(): ?string
    {
        // AsPivot otherwise inherits this column name from the parent model.
        return null;
    }

    protected function casts(): array
    {
        return ['created_at' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(CustomerProfile::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
