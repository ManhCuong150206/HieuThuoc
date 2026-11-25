<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    public function paginateComments(?int $focus = null): LengthAwarePaginator
    {
        $comments = $this->comments()->with('account.profile')->orderByDesc('id');
        $perPage = 10;
        $page = $focus && (clone $comments)->whereKey($focus)->exists()
            ? intdiv((clone $comments)->where('id', '>', $focus)->count(), $perPage) + 1
            : null;

        return $comments->paginate($perPage, page: $page);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'PUBLISHED')->whereDate('published_at', '<=', today());
    }

    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->published()->orderByDesc('published_at')->orderByDesc('id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED' && $this->published_at?->lte(today()) === true;
    }

    public function healthGuide(): ?array
    {
        $guide = config('health_articles')[$this->cover_url] ?? null;

        // Only expand the original editorial copy. Staff edits and other posts use their saved text.
        if ($guide && $guide['title'] === $this->title && $guide['content'] === str_replace("\r\n", "\n", $this->content)) {
            return $guide;
        }

        return null;
    }
}
