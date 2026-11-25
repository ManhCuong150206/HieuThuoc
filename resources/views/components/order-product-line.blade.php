@props(['item', 'href' => null])
<div class="order-card-content">
    @if ($href)
        <a href="{{ $href }}" class="order-card-art" aria-label="Xem {{ $item->product_name }}"><x-product-image :product="$item->imageProduct()" width="64" height="64" /></a>
    @else
        <div class="order-card-art" aria-hidden="true"><x-product-image :product="$item->imageProduct()" width="64" height="64" /></div>
    @endif
    <div class="order-card-copy">
        <p class="order-product-name">
            @if ($href)<a href="{{ $href }}">{{ $item->product_name }}</a>@else{{ $item->product_name }}@endif
        </p>
        <p>Số lượng: {{ $item->quantity }} · {{ number_format($item->unit_price, 0, ',', '.') }}đ / sản phẩm</p>
    </div>
    <strong class="order-line-price">{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ</strong>
</div>
