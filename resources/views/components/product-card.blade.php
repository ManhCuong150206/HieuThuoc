@props(['product'])
<article class="product-card">
    <a href="{{ route('products.show', $product) }}" class="product-card-art" aria-label="{{ $product->name }}">
        <x-product-image :product="$product" />
    </a>
    <x-badge>{{ $product->quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}</x-badge>
    <div class="product-card-copy">
        <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
        <p>{{ $product->category?->name }}@if ($product->brand) · {{ $product->brand }}@endif</p>
    </div>
    <p class="product-price">{{ number_format($product->price, 0, ',', '.') }}đ</p>
    <x-button class="full-width" :href="route('products.show', $product)" :aria-label="'Xem chi tiết: '.$product->name">Xem chi tiết</x-button>
</article>
