@props(['product'])
<article class="card product-card">
    <div class="product-card-art m-zoom">
        <x-product-image :product="$product" />
    </div>
    <x-badge :variant="$product->quantity > 0 ? 'pale' : 'muted'">{{ $product->quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}</x-badge>
    <div class="product-card-body">
        <div class="product-card-copy">
            <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
            <p>{{ $product->category?->name }}@if ($product->brand)
                    · {{ $product->brand }}
                @endif
            </p>
        </div>
        <p class="product-price"><x-sale-price :price="$product->saleUnitPrice()" :regular-price="$product->regularUnitPrice()" /></p>
        @if (
            $product->is_active &&
                $product->quantity > 0 &&
                $product->price > 0 &&
                (!$product->expiry_date || $product->expiry_date->gte(today())))
            <form class="product-card-actions" method="POST" action="{{ route('cart.store', $product) }}" data-ajax-action="cart">
                @csrf<input type="hidden" name="quantity" value="1">
                <x-button type="submit" name="buy_now" value="1" class="full-width" :aria-label="'Mua ngay: ' . $product->name">Mua ngay</x-button>
                <x-button type="submit" variant="outline" class="full-width" :aria-label="'Thêm vào giỏ hàng: ' . $product->name">Thêm vào giỏ hàng</x-button>
            </form>
        @endif
    </div>
</article>
