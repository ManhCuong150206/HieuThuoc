@extends('layouts.app')
@section('title', $product->name)
@section('content')
<div class="site-container page-stack product-page">
    <x-breadcrumb :items="[['label' => $category->name, 'href' => route('categories.show', $category)]]" />
    <section class="product-detail" aria-labelledby="product-title">
        <div class="gallery-main"><x-product-image :product="$product" loading="eager" /></div>
        <div class="product-detail-copy">
            @if ($product->brand)<p class="detail-brand">Thương hiệu <strong>{{ $product->brand }}</strong></p>@endif
            <h1 id="product-title">{{ $product->name }}</h1>
            <p class="detail-meta">Mã sản phẩm: {{ $product->id }}</p>
            <div class="detail-price-row">
                <p class="detail-price"><x-sale-price :price="$product->saleUnitPrice()" :regular-price="$product->regularUnitPrice()" /></p>
                <x-badge>{{ $product->quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}</x-badge>
            </div>
            @php($canBuy = $product->canPurchase())
            @if ($canBuy)
                <form method="POST" action="{{ route('cart.store', $product) }}" class="detail-purchase" data-ajax-action="cart">
                    @csrf
                    <div class="detail-quantity">
                        <label for="buy-quantity">Chọn số lượng</label>
                        <div class="quantity-stepper" data-quantity-stepper>
                            <button type="button" data-quantity-step="-1" aria-label="Giảm số lượng" hidden>−</button>
                            <input id="buy-quantity" name="quantity" type="number" min="1" max="{{ $product->maxCartQuantity() }}" value="1" required>
                            <button type="button" data-quantity-step="1" aria-label="Tăng số lượng" hidden>+</button>
                        </div>
                    </div>
                    <div class="detail-actions">
                        <x-button type="submit">Thêm vào giỏ</x-button>
                        <x-button type="submit" name="buy_now" value="1" variant="outline">Mua ngay</x-button>
                    </div>
                </form>
            @else
                <p class="checkout-note">Sản phẩm hiện chưa thể đặt mua.</p>
                <x-button variant="outline" :href="route('categories.show', $category)">Xem sản phẩm cùng danh mục</x-button>
            @endif
            <div class="detail-facts">
                <p>Số lượng còn: {{ $product->quantity }}</p>
                @if ($product->expiry_date)<p>Hạn sử dụng: {{ $product->expiry_date->format('d/m/Y') }}</p>@endif
            </div>
            @if ($product->used_for)<p>{{ $product->used_for }}</p>@endif
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
    @include('pages.products.reviews')
    @if ($relatedProducts->isNotEmpty())
        <x-section-title title="Sản phẩm cùng danh mục" />
        <div class="product-grid">
            @foreach ($relatedProducts as $related)<x-product-card :product="$related" />@endforeach
        </div>
    @endif
</div>
@endsection
