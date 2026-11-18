@extends('layouts.app')
@section('title', 'Trang chủ')
@section('body-class', 'storefront home')
@section('content')
<section class="home-hero" aria-labelledby="home-title">
    <div class="site-container home-hero-inner">
        <div class="hero-copy">
            <x-badge>AN TÂM CHĂM SÓC • TRỌN VẸN MỖI NGÀY</x-badge>
            <h1 id="home-title">Sức khỏe của bạn,<br>sự an tâm của cả nhà.</h1>
            <p>Tìm sản phẩm chăm sóc sức khỏe phù hợp.<br>Đặt hàng dễ dàng, chăm sóc tận tâm.</p>
            <div class="hero-actions">
                <x-button :href="route('products.index')">Khám phá sản phẩm</x-button>
                <x-button variant="outline">Tư vấn cùng dược sĩ</x-button>
            </div>
        </div>
        <div class="hero-illustration">
            <div class="hero-promotion"><span>CHĂM SÓC CHO CẢ NHÀ</span></div>
            <div class="hero-packs">
                <a href="{{ route('products.index') }}" aria-label="Khám phá sản phẩm chăm sóc vết thương">
                    <img src="{{ asset('images/'.rawurlencode('Băng cá nhân Urgo Waterproof 2x7,2cm - Hộp 50 miếng.png')) }}" alt="Băng cá nhân Urgo Waterproof" width="240" height="170" loading="eager">
                </a>
                <a href="{{ route('products.index') }}" aria-label="Khám phá sản phẩm chăm sóc da">
                    <img src="{{ asset('images/'.rawurlencode('Bioderma Sensibio H2O - Chai 500ml.png')) }}" alt="Nước tẩy trang Bioderma Sensibio H2O" width="240" height="170" loading="eager">
                </a>
            </div>
            <div class="hero-caption">
                <p>Những điều nhỏ, chăm sóc lớn.</p>
                <a href="{{ route('products.index') }}">Khám phá sản phẩm chăm sóc mỗi ngày →</a>
            </div>
        </div>
    </div>
</section>
<div class="site-container home-content">
    <ul class="promises-grid">
        @foreach ($promises as [$icon, $title, $copy])
            <li class="promise"><x-icon :name="$icon" :size="28" /><div><h2>{{ $title }}</h2><p>{{ $copy }}</p></div></li>
        @endforeach
    </ul>
    <section class="home-section">
        <x-section-title title="Bạn đang tìm sản phẩm nào?" />
        <div class="category-grid">
            @foreach ($categories as $category)
                <a class="category-card" href="{{ route('categories.show', $category) }}">
                    <div><h3>{{ $category->name }}</h3><p>{{ $category->products_count }} sản phẩm</p></div>
                </a>
            @endforeach
        </div>
    </section>
    <section class="home-section">
        <div class="section-heading-row">
            <x-section-title title="Sản phẩm mới cập nhật" />
            <x-button variant="outline" class="view-all" :href="route('products.index')">Xem tất cả →</x-button>
        </div>
        <div class="product-grid">
            @foreach ($products as $product)<x-product-card :product="$product" />@endforeach
        </div>
    </section>
    <section class="new-arrivals">
        <x-icon name="new-plus" :size="40" />
        <div><h2>Mới về tại An Tâm</h2><p>Khám phá những sản phẩm vừa được bổ sung cho tủ thuốc gia đình.</p></div>
        <x-button :href="route('products.index')">Khám phá ngay</x-button>
    </section>
    <section class="home-section" id="goc-suc-khoe">
        <x-section-title eyebrow="GÓC SỨC KHỎE" title="Hiểu đúng, sống khỏe mỗi ngày" />
        <div class="articles-grid">
            @foreach ($articles as [$title, $copy])
                <article class="article-card">
                    <x-badge>KIẾN THỨC SỨC KHỎE</x-badge>
                    <h3>{{ $title }}</h3><p>{{ $copy }}</p><span class="article-link">Đọc bài viết →</span>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
