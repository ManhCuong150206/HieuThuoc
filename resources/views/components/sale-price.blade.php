@props(['price', 'regularPrice'])
<span {{ $attributes->class('sale-price') }}>
    @if ($price < $regularPrice)
        <del class="sale-price-original"><span class="sr-only">Giá gốc </span>{{ number_format($regularPrice, 0, ',', '.') }}đ</del>
        <span class="sr-only">Giá khuyến mãi </span>
    @endif
    <span>{{ number_format($price, 0, ',', '.') }}đ</span>
</span>
