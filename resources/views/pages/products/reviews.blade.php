<section class="checkout-panel product-reviews" id="danh-gia" aria-labelledby="product-reviews-title">
    <h2 id="product-reviews-title">Đánh giá từ người mua @if ($reviewCount)<span>({{ $reviewCount }} đánh giá)</span>@endif</h2>
    @if (!$reviewCount)
        <div class="product-reviews-empty">
            <span class="product-reviews-empty-art" aria-hidden="true">★★★★★</span>
            <h3>Chưa có đánh giá nào</h3>
            <p>Bạn có thể chia sẻ trải nghiệm sau khi hoàn thành đơn hàng.</p>
        </div>
    @else
        <div class="product-reviews-overview">
            <div class="product-review-average">
                <span>Điểm trung bình</span>
                <strong>{{ number_format($averageRating, 1, ',', '.') }} <span>/ 5</span></strong>
                <a class="text-action" href="{{ route('account.orders') }}">Đánh giá từ đơn đã mua</a>
            </div>
            <div class="product-review-distribution">
                @foreach (range(5, 1) as $stars)
                    @php($count = $ratingCounts->get($stars, 0))
                    <div class="product-review-meter">
                        <span class="product-review-stars" aria-hidden="true">{{ str_repeat('★', $stars) }}<span>{{ str_repeat('☆', 5 - $stars) }}</span></span>
                        <meter min="0" max="{{ $reviewCount }}" value="{{ $count }}" aria-label="{{ $stars }} sao, {{ $count }} đánh giá"></meter>
                        <span aria-hidden="true">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <nav class="product-review-filters" aria-label="Lọc đánh giá theo số sao">
            <a href="{{ route('products.show', $product) }}#danh-gia" @if (!$rating) aria-current="true" @endif>Tất cả</a>
            @foreach (range(5, 1) as $stars)
                <a href="{{ route('products.show', ['product' => $product, 'rating' => $stars]) }}#danh-gia" @if ($rating === $stars) aria-current="true" @endif>{{ $stars }} sao</a>
            @endforeach
        </nav>
        @forelse ($reviews as $review)
            @php($name = $review->orderItem?->order?->user?->full_name ?? 'Khách hàng')
            @php($anchor = 'danh-gia-'.$review->id)
            <article class="review-entry product-review-entry discussion-entry" id="{{ $anchor }}">
                <span class="product-review-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
                <div>
                    <h3>{{ $name }}</h3>
                    <span class="sr-only">{{ $review->rating }} trên 5 sao</span>
                    <span class="product-review-stars" aria-hidden="true">{{ str_repeat('★', $review->rating) }}<span>{{ str_repeat('☆', 5 - $review->rating) }}</span></span>
                    @if ($review->comment)<p>{{ $review->comment }}</p>@endif
                    @if ($review->created_at)<time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d/m/Y') }}</time>@endif
                    <x-discussion-reply :entry="$review" :action="route('admin.reviews.reply', [$product, $review])" :anchor="$anchor" />
                </div>
            </article>
        @empty
            <p class="product-review-no-results">Chưa có đánh giá {{ $rating }} sao.</p>
        @endforelse
        <x-pagination :paginator="$reviews" />
    @endif
</section>
