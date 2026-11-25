<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostComment extends Model
{
    protected $fillable = ['account_id', 'body'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function authorName(): string
    {
        return $this->account->isAdmin() ? 'An Tâm Pharmacy' : ($this->account->profile?->full_name ?? 'Khách hàng');
    }
}
