<?php

namespace App\Queries;

use App\Models\Product;
use App\Models\Review;

class ProductReviewQuery
{
    public function summary(Product $product, ?int $rating, ?int $focus): array
    {
        $publicReviews = Review::publiclyVisible()
            ->whereHas('orderItem', fn ($q) => $q->where('product_id', $product->id));
        $ratingCounts = (clone $publicReviews)->selectRaw('rating, count(*) as total')->groupBy('rating')->pluck('total', 'rating');
        $reviewCount = $ratingCounts->sum();
        $averageRating = $reviewCount ? $ratingCounts->map(fn ($count, $stars) => $count * $stars)->sum() / $reviewCount : null;
        $reviews = $publicReviews->with('orderItem.order.user')->when($rating, fn ($q) => $q->where('rating', $rating))
            ->orderByDesc('id');
        $perPage = 10;
        $page = $focus && (clone $reviews)->whereKey($focus)->exists()
            ? intdiv((clone $reviews)->where('id', '>', $focus)->count(), $perPage) + 1
            : null;

        return [
            'reviews' => $reviews->paginate($perPage, page: $page),
            'ratingCounts' => $ratingCounts,
            'reviewCount' => $reviewCount,
            'averageRating' => $averageRating,
        ];
    }
}
