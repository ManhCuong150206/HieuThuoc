@props(['product', 'loading' => 'lazy'])
<img src="{{ $product->imageSrc() }}" alt="{{ $product->name }}" loading="{{ $loading }}" data-product-image {{ $attributes }}>
<span class="product-image-fallback" hidden>Chưa có ảnh sản phẩm</span>
