@extends('layouts.app')
@section('title', $product['name'])
@section('content')
<div class="site-container page-stack">
    <x-breadcrumb :items="[['label' => $category['name'], 'href' => route('categories.show', $category['slug'])], ['label' => $product['name']]]" />
    <section class="product-detail">
        <div class="product-gallery">
            <div class="gallery-main"><x-icon :name="$product['gallery'][0]" /></div>
            <div class="gallery-thumbs">
                @foreach ($product['thumbnails'] as $art)
                    <div><x-icon :name="$art" /></div>
                @endforeach
            </div>
            <p>Hình minh họa bao bì • Sản phẩm mẫu</p>
        </div>
        <div class="product-detail-copy">
            <x-badge>{{ $product['typeLabel'] }}</x-badge>
            <h1>{{ $product['name'] }}</h1>
            <p class="detail-meta">{{ $product['package'] }} • Mã sản phẩm: {{ $product['sku'] }}</p>
            <p class="detail-rating">★ 4,8 · 24 đánh giá đã mua hàng</p>
            <div class="detail-price-row">
                <p class="detail-price">{{ $product['price'] }}</p>
                <x-badge>Còn hàng</x-badge>
            </div>
            @if ($product['facts'])
                <ul class="detail-facts">
                    @foreach ($product['facts'] as $fact)<li>{{ $fact }}</li>@endforeach
                </ul>
            @endif
            <div class="product-notice">
                <p>Lưu ý trước khi sử dụng</p>
                <p>Thực phẩm bổ sung không phải là thuốc và không có tác dụng thay thế thuốc chữa bệnh.</p>
            </div>
            <div class="detail-quantity"><span>Số lượng</span><x-quantity /></div>
            <div class="detail-actions"><x-button>Thêm vào giỏ</x-button><x-button variant="outline">♡ &nbsp; Yêu thích</x-button></div>
            <p class="detail-support">Cần thêm thông tin? Nhắn dược sĩ để được hỗ trợ.</p>
        </div>
    </section>
    <section class="product-information">
        <nav class="detail-tabs" aria-label="Nội dung sản phẩm">
            @foreach ($information as [$id, $tab])
                <a href="{{ route('products.show', $product['slug']) }}#{{ $id }}" @if ($loop->first) class="is-active" @endif>{{ $tab }}</a>
            @endforeach
        </nav>
        @foreach ($information as [$id, $tab, $title, $description])
            <div id="{{ $id }}"><h2>{{ $title }}</h2><p>{{ $description }}</p></div>
        @endforeach
        <x-button variant="outline">Viết đánh giá</x-button>
    </section>
    <x-section-title title="Bạn có thể quan tâm" />
    <div class="product-grid">
        @foreach ($relatedProducts as $related)<x-product-card :product="$related" />@endforeach
    </div>
</div>
@endsection
