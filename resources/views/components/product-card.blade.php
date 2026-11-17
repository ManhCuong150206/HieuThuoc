@props(['product'])
<article class="product-card">
    <a href="{{ route('products.show', $product['slug']) }}" class="product-card-art" aria-label="{{ $product['name'] }}">
        <x-icon :name="$product['image']" />
    </a>
    <x-badge>{{ $product['badge'] }}</x-badge>
    <div class="product-card-copy">
        <h3><a href="{{ route('products.show', $product['slug']) }}">{{ $product['name'] }}</a></h3>
        <p>{{ $product['package'] }} • {{ $product['type'] }}</p>
    </div>
    <p class="product-price">{{ $product['price'] }}</p>
    <x-button class="full-width" :aria-label="'Thêm vào giỏ: '.$product['name']">Thêm vào giỏ</x-button>
</article>
