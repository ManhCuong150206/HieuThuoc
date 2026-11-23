@extends('layouts.app')
@section('title', 'Nhà thuốc trực tuyến cho cả gia đình')
@section('body-class', 'storefront home')
@php
    $productTotal = $categories->sum('products_count');
@endphp
@section('content')
    <section class="hero hero--intro" data-enter aria-labelledby="home-title">
        <div class="site-container wrap">
            <h1 id="home-title">Nhà thuốc trực tuyến <span>cho cả gia đình</span></h1>
            <p class="hero__lead">Thuốc không kê đơn, vitamin và đồ chăm sóc cá nhân, ghi rõ thành phần, cách
                dùng và hạn sử dụng. Giao tận nhà, phí 25.000đ mỗi đơn.</p>
        </div>
    </section>

    <section class="home-section" id="danh-muc" aria-labelledby="danh-muc-title">
        <div class="site-container wrap home-split">
            <div class="home-split-text">
                <x-section-title id="danh-muc-title" title="Thuốc, vitamin và dụng cụ y tế" :description="$productTotal . ' sản phẩm đang bán, chia theo ' . $categories->count() . ' danh mục.'" />
                <ul class="category-rows">
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('categories.show', $category) }}">
                                <span class="category-rows-name">{{ $category->name }}</span>
                                <span class="category-rows-count">{{ $category->products_count }} sản phẩm</span>
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

    <section class="home-section" id="san-pham-moi" aria-labelledby="san-pham-moi-title">
        <div class="site-container wrap">
            <div class="home-section-head">
                <x-section-title id="san-pham-moi-title" :title="$products->count() . ' sản phẩm mới về kệ'"
                    description="Những sản phẩm vừa được thêm vào nhà thuốc, kèm giá bán và tình trạng còn hàng." />
                <a class="link" href="{{ route('products.index') }}">Xem tất cả {{ $productTotal }} sản phẩm</a>
            </div>
            <div class="product-grid">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-section home-band on-dark" id="dat-hang" aria-labelledby="dat-hang-title">
        <div class="site-container wrap home-band-grid">
            <div>
                <x-section-title id="dat-hang-title" title="4 bước đặt thuốc giao tận nhà"
                    description="Từ giỏ hàng đến tay bạn, theo dõi được trong tài khoản." />
                <ol class="order-steps">
                    <li><span class="order-steps-no">1</span>
                        <p>Thêm sản phẩm còn hàng vào giỏ.</p>
                    </li>
                    <li><span class="order-steps-no">2</span>
                        <p>Kiểm tra số lượng, điền tên, số điện thoại và địa chỉ nhận hàng.</p>
                    </li>
                    <li><span class="order-steps-no">3</span>
                        <p>Chọn ưu đãi nếu có rồi xác nhận đơn.</p>
                    </li>
                    <li><span class="order-steps-no">4</span>
                        <p>Hủy được trong tài khoản trước khi đơn chuyển sang Đang giao.</p>
                    </li>
                </ol>
            </div>
            <div class="home-band-side">
                <p>Phí giao <strong>25.000đ</strong> mỗi đơn, hiện trước khi bạn xác nhận. Hiện nhận tiền mặt; thanh toán mã
                    QR đang được bổ sung.</p>
                <a class="link" href="{{ route('help') }}#van-chuyen">Đọc quy định giao và hủy đơn</a>
                <figure class="frame home-band-photo"><img src="{{ asset('images/home/bao-thach.jpg') }}"
                        alt="Gói 5 cuộn băng y tế Bảo Thạch" width="880" height="880" loading="lazy" decoding="async">
                </figure>
            </div>
        </div>
    </section>

    <section class="home-section" id="thong-tin-san-pham" aria-labelledby="thong-tin-title">
        <div class="site-container wrap home-info">
            <div class="home-info-text">
                <x-section-title id="thong-tin-title" title="5 mục thông tin trên mỗi sản phẩm"
                    description="Trang của mỗi sản phẩm ghi số lượng còn, hạn sử dụng và những mục nhà thuốc đã cập nhật:" />
                <ul class="info-rows">
                    <li>Thành phần</li>
                    <li>Công dụng</li>
                    <li>Cách dùng</li>
                    <li>Chống chỉ định</li>
                    <li>Tác dụng phụ</li>
                </ul>
                <p class="home-info-note">Đánh giá chỉ đến từ đơn đã hoàn thành và được nhà thuốc duyệt trước khi hiển thị.
                </p>
            </div>
            <div class="home-info-pair">
                <figure class="frame"><img src="{{ asset('images/home/listerine-large.jpg') }}"
                        alt="Chai nước súc miệng Listerine Cool Mint 250ml" width="652" height="1100" loading="lazy"
                        decoding="async"></figure>
                <figure class="frame"><img src="{{ asset('images/home/sensodyne-fresh.jpg') }}"
                        alt="Tuýp kem đánh răng Sensodyne Fresh Mint 100g" width="459" height="1100" loading="lazy"
                        decoding="async"></figure>
            </div>
        </div>
    </section>

    @if ($articles->isNotEmpty())
        <section class="home-section" id="goc-suc-khoe" aria-labelledby="goc-suc-khoe-title">
            <div class="site-container wrap">
                <div class="home-section-head">
                    <x-section-title id="goc-suc-khoe-title" title="Bài viết sức khỏe"
                        description="Bài viết mới nhất do nhà thuốc đăng." />
                    <a class="link" href="{{ route('posts.index') }}">Đọc tất cả bài viết</a>
                </div>
                <div class="home-articles">
                    @foreach ($articles as $article)
                        <article class="card home-article">
                            <p class="eyebrow">{{ $article->published_at?->format('d/m/Y') }}</p>
                            <h3><a href="{{ route('posts.show', $article) }}">{{ $article->title }}</a></h3>
                            <p>{{ \Illuminate\Support\Str::limit($article->content, 140) }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

@section('closing')
    <div class="footer-close-row">
        <div>
            <h2>Thuốc và đồ chăm sóc sức khỏe giao tận nhà</h2>
            <p>{{ $productTotal }} sản phẩm trong {{ $categories->count() }} danh mục. Phí giao 25.000đ mỗi đơn, trả tiền
                mặt.</p>
        </div>
        <x-button :href="route('products.index')">Xem sản phẩm</x-button>
    </div>
@endsection
