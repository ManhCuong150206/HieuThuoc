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
    @if ($relatedProducts->isNotEmpty())
        <x-section-title title="Sản phẩm cùng danh mục" />
        <div class="product-grid">
            @foreach ($relatedProducts as $related)<x-product-card :product="$related" />@endforeach
        </div>
    @endif
</div>
@endsection
