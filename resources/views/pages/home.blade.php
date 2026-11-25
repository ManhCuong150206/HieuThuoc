@extends('layouts.app')
@section('title', 'Nhà thuốc trực tuyến cho cả gia đình')
@section('body-class', 'storefront home')
@section('content')
    <section class="hero hero--intro" data-enter aria-labelledby="home-title">
        <div class="site-container wrap">
            <p class="hero-brand">An Tâm Pharmacy</p>
            <h1 id="home-title">Nhà thuốc trực tuyến <span class="hero-highlight">cho cả gia đình</span></h1>
            <p class="hero__lead">Tìm thuốc, vitamin và sản phẩm chăm sóc cá nhân cho gia đình. Xem thành phần, cách dùng và đặt giao tận nhà.</p>
        </div>
    </section>

    <section class="home-section" id="danh-muc" aria-labelledby="danh-muc-title">
        <div class="site-container wrap home-split">
            <div class="home-split-text">
                <x-section-title id="danh-muc-title" title="Thuốc, vitamin và dụng cụ y tế" />
                <ul class="category-rows">
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('categories.show', $category) }}">
                                <span class="category-rows-name">{{ $category->name }}</span>

                                <svg class="m-arrow" width="20" height="20" viewBox="0 0 20 20" aria-hidden="true">
                                    <path class="m-arrow__stem" d="M3 10h13" fill="none" stroke="currentColor"
                                        stroke-width="1.5" />
                                    <path class="m-arrow__head" d="m11 5 5 5-5 5" fill="none" stroke="currentColor"
                                        stroke-width="1.5" />
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <figure class="home-split-picture frame frame--photo" data-moment="picture-develops">
                <img src="{{ asset('images/home/blackmores-large.jpg') }}" alt="Hộp Blackmores Bio C 1000mg, 31 viên"
                    width="1300" height="1300" decoding="async">
            </figure>
        </div>
    </section>

    @if ($saleProducts->isNotEmpty())
        <section class="home-section home-offers" id="san-pham-giam-gia" aria-labelledby="san-pham-giam-gia-title">
            <div class="site-container wrap">
                <div class="home-section-head">
                    <x-section-title id="san-pham-giam-gia-title" title="Sản phẩm đang giảm giá" />
                    <a class="link" href="{{ route('products.index', ['on_sale' => 1]) }}">Xem tất cả sản phẩm giảm giá</a>
                </div>
                <div @class(['product-grid', 'home-offers-grid--compact' => $saleProducts->count() <= 2])>
                    @foreach ($saleProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="home-section" id="san-pham-ban-chay" aria-labelledby="san-pham-ban-chay-title">
        <div class="site-container wrap">
            <div class="home-section-head">
                <x-section-title id="san-pham-ban-chay-title" title="Sản phẩm bán chạy" />
                <a class="link" href="{{ route('products.index') }}">Xem tất cả sản phẩm</a>
            </div>
            <div class="product-grid">
                @forelse ($bestSellers as $product)
                    <x-product-card :product="$product" />
                @empty
                    <p class="best-sellers-empty">Chưa có sản phẩm bán chạy.</p>
                @endforelse
            </div>
        </div>
    </section>

    @if ($articles->isNotEmpty())
        <section class="home-section home-health" id="goc-suc-khoe" aria-labelledby="goc-suc-khoe-title">
            <div class="site-container wrap">
                <div class="home-section-head">
                    <x-section-title id="goc-suc-khoe-title" title="Góc sức khỏe" />
                    <a class="link" href="{{ route('posts.index') }}">Đọc tất cả bài viết</a>
                </div>
                <div class="home-articles">
                    @foreach ($articles as $article)
                        <article @class(['home-article', 'home-article--featured' => $loop->first])>
                            <a class="home-article-link" href="{{ route('posts.show', $article) }}">
                                @if ($article->cover_url)
                                    <div class="home-article-cover">
                                        <img src="{{ $article->cover_url }}" alt="" loading="lazy" decoding="async">
                                    </div>
                                @endif
                                <div class="home-article-copy">
                                    @if ($loop->first)<p class="home-article-label">Bài viết nổi bật</p>@endif
                                    <h3>{{ $article->title }}</h3>
                                    @if ($loop->first)
                                        <p>{{ $article->healthGuide()['summary'] ?? \Illuminate\Support\Str::limit($article->content, 145, preserveWords: true) }}</p>
                                    @endif
                                    <div class="home-article-meta">
                                        <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('d/m/Y') }}</time>
                                        <span aria-hidden="true">Đọc bài <span>↗</span></span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
