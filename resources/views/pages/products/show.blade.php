@extends('layouts.app')
@section('title', $product->name)
@section('content')
<div class="site-container page-stack">
    <x-breadcrumb :items="[['label' => $category->name, 'href' => route('categories.show', $category)], ['label' => $product->name]]" />
    <section class="product-detail">
        <div class="product-gallery"><div class="gallery-main"><x-product-image :product="$product" loading="eager" /></div></div>
        <div class="product-detail-copy">
            <x-badge>{{ $category->name }}</x-badge>
            <h1>{{ $product->name }}</h1>
            <p class="detail-meta">Mã sản phẩm: {{ $product->id }}@if ($product->brand) · {{ $product->brand }}@endif</p>
            <div class="detail-price-row">
                <p class="detail-price">{{ number_format($product->price, 0, ',', '.') }}đ</p>
                <x-badge>{{ $product->quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}</x-badge>
            </div>
            <p>Số lượng còn: {{ $product->quantity }}</p>
            @if ($product->expiry_date)<p>Hạn sử dụng: {{ $product->expiry_date->format('d/m/Y') }}</p>@endif
            @if ($product->used_for)<p>{{ $product->used_for }}</p>@endif
            <x-button variant="outline" :href="route('categories.show', $category)">Xem sản phẩm cùng danh mục</x-button>
            @php($canBuy = $product->quantity > 0 && $product->price > 0 && (!$product->expiry_date || $product->expiry_date->gte(today())))
            @if ($canBuy)
                <form method="POST" action="{{ route('cart.store', $product) }}" class="purchase-form" data-ajax-action="cart">
                    @csrf
                    <label for="buy-quantity">Số lượng</label>
                    <input id="buy-quantity" name="quantity" type="number" min="1" max="{{ min(999, $product->quantity) }}" value="1" required>
                    <x-button type="submit">Thêm vào giỏ</x-button>
                    <x-button type="submit" name="buy_now" value="1" variant="outline">Mua ngay</x-button>
                </form>
            @else
                <p class="checkout-note">Sản phẩm hiện chưa thể đặt mua. Vui lòng chọn sản phẩm khác.</p>
            @endif
            <form method="POST" action="{{ route('favorites.store', $product) }}" data-ajax-action="favorite" data-product-id="{{ $product->id }}">@csrf<x-button type="submit" variant="soft">Lưu yêu thích</x-button></form>
        </div>
    </section>
    <section class="product-information" aria-labelledby="product-information-title">
        <h2 id="product-information-title">Thông tin sản phẩm</h2>
        @forelse (collect(['Thành phần' => $product->composition, 'Công dụng' => $product->used_for, 'Cách dùng' => $product->how_to_use, 'Chống chỉ định' => $product->contraindications, 'Tác dụng phụ' => $product->side_effects])->filter() as $label => $content)
            <div><h3>{{ $label }}</h3><p>{{ $content }}</p></div>
        @empty
            <p>Thông tin chi tiết đang được cập nhật.</p>
        @endforelse
    </section>
    <section class="checkout-panel" id="danh-gia">
        <h2>Đánh giá từ khách hàng</h2>
        @forelse ($reviews as $review)
            <article class="review-entry"><strong>{{ $review->rating }}/5 — {{ $review->orderItem?->order?->user?->full_name ?? 'Khách hàng' }}</strong><p>{{ $review->comment }}</p></article>
        @empty<p>Chưa có đánh giá được duyệt.</p>@endforelse
        <x-pagination :paginator="$reviews" />
    </section>
    @if ($relatedProducts->isNotEmpty())
        <x-section-title title="Sản phẩm cùng danh mục" />
        <div class="product-grid">
            @foreach ($relatedProducts as $related)<x-product-card :product="$related" />@endforeach
        </div>
    @endif
</div>
@endsection
