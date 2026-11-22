<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected $fillable = ['author_account_id', 'title', 'content', 'cover_url', 'status', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'date'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'author_account_id');
    }
}
