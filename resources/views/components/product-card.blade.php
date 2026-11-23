@props(['product'])
<article class="card product-card">
    <div class="product-card-art m-zoom">
        <x-product-image :product="$product" />
    </div>
    <div class="product-card-body">
        <div class="product-card-copy">
            <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
            <p>{{ $product->category?->name }}@if ($product->brand)
                    · {{ $product->brand }}
                @endif
            </p>
        </div>
        <x-badge :variant="$product->quantity > 0 ? 'pale' : 'muted'">{{ $product->quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}</x-badge>
        <p class="product-price">{{ number_format($product->price, 0, ',', '.') }}đ</p>
        @if (
            $product->is_active &&
                $product->quantity > 0 &&
                $product->price > 0 &&
                (!$product->expiry_date || $product->expiry_date->gte(today())))
            <form method="POST" action="{{ route('cart.store', $product) }}" data-ajax-action="cart">
                @csrf<input type="hidden" name="quantity" value="1">
                <x-button type="submit" variant="outline" class="full-width" :aria-label="'Thêm vào giỏ: ' . $product->name">Thêm vào giỏ</x-button>
            </form>
        @endif
    </div>
</article>
