<header class="site-header nav" data-site-header>
    <a class="skip-link" href="#main-content">Bỏ qua, đến nội dung chính</a>
    <div class="clinic-topbar">
        <div class="site-container">
            <span>Giao tận nhà · Phí giao 25.000đ mỗi đơn</span>
            <a href="{{ route('help') }}#van-chuyen">Thông tin vận chuyển</a>
        </div>
    </div>
    <div class="site-container wrap header-main">
        <a class="header-logo" href="{{ route('home') }}" aria-label="An Tâm Pharmacy — Trang chủ">
            <img src="{{ asset('images/logo-mark.png') }}" alt="An Tâm Pharmacy" width="242" height="97">
        </a>
        <div class="header-drawer" id="header-drawer">
            <form class="header-search" role="search" action="{{ route('products.index') }}" method="GET">
                <label for="header-search-sheet"><img src="{{ asset('images/figma/search.svg') }}" alt=""
                        width="20" height="20">Tìm</label>
                <input id="header-search-sheet" type="search" name="q" value="{{ request('q') }}"
                    maxlength="100" placeholder="Tìm thuốc, vitamin, đồ chăm sóc…">
                <button type="submit" class="search-submit">Tìm kiếm</button>
            </form>
            <nav class="header-navigation" aria-label="Điều hướng chính">
                <ul class="header-menu">
                    <li class="header-categories" data-category-menu>
                        <button class="header-category-trigger @if (request()->routeIs('products.*', 'categories.show')) is-active @endif"
                            type="button" aria-expanded="false" aria-controls="header-category-list">Danh mục <svg
                                width="12" height="12" viewBox="0 0 12 12" aria-hidden="true">
                                <path d="M2.5 4.5 6 8l3.5-3.5" fill="none" stroke="currentColor"
                                    stroke-width="1.5" />
                            </svg></button>
                        <ul class="header-category-list" id="header-category-list" hidden>
                            <li><a href="{{ route('products.index') }}"
                                    @if (request()->routeIs('products.index')) aria-current="page" @endif>Tất cả sản phẩm</a>
                            </li>
                            @foreach ($navigationCategories as $navigationCategory)
                                <li><a href="{{ route('categories.show', $navigationCategory) }}"
                                        @if (request()->routeIs('categories.show') &&
                                                (string) request()->route('category')?->id === (string) $navigationCategory->id) aria-current="page" @endif>{{ $navigationCategory->name }}</a>
                                </li>
                            @endforeach
                            <li class="header-category-card">
                                <a href="{{ route('products.index') }}">
                                    <img src="{{ asset('images/home/blackmores.jpg') }}" alt="" width="491"
                                        height="760" loading="lazy">
                                    <span><strong>Xem sản phẩm</strong><br><span class="eyebrow">Tìm theo tên hoặc
                                            thương hiệu</span></span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li><a href="{{ route('posts.index') }}"
                            @if (request()->routeIs('posts.*')) aria-current="page" @endif>Tin tức sức khỏe</a></li>
                    <li><a href="{{ route('help') }}"
                            @if (request()->routeIs('help')) aria-current="page" @endif>Hướng dẫn mua hàng</a></li>
                </ul>
            </nav>
            <div class="header-drawer-extra">
                @auth
                    <a class="btn btn--secondary"
                        href="{{ route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'account.index') }}">Tài khoản
                        {{ auth()->user()->username }}</a>
                    <form method="POST" action="{{ route('logout') }}" class="header-drawer-logout">
                        @csrf
                        <button type="submit" class="btn btn--secondary full-width">Đăng xuất</button>
                    </form>
                @else
                    <a class="btn btn--secondary" href="{{ route('login') }}">Đăng nhập</a>
                    <a class="btn btn--secondary" href="{{ route('register') }}">Đăng ký tài khoản</a>
                @endauth
            </div>
        </div>
        <form class="header-search" role="search" action="{{ route('products.index') }}" method="GET">
            <label for="header-search-bar"><img src="{{ asset('images/figma/search.svg') }}" alt=""
                    width="20" height="20">Tìm</label>
            <input id="header-search-bar" type="search" name="q" value="{{ request('q') }}" maxlength="100"
                placeholder="Tìm thuốc, vitamin, đồ chăm sóc…">
            <button type="submit" class="search-submit">Tìm kiếm</button>
        </form>
        <div class="header-actions">
            @auth
                <a class="header-action header-account header-account--signed-in @if (request()->routeIs('account.*')) is-active @endif"
                    href="{{ route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'account.index') }}"
                    aria-label="Tài khoản {{ auth()->user()->username }}">
                    <img src="{{ asset('images/figma/user.svg') }}" alt="" width="24"
                        height="24"><span>{{ auth()->user()->username }}</span>
                </a>
            @else
                <a class="header-action header-account @if (request()->routeIs('login', 'register')) is-active @endif"
                    href="{{ route('login') }}" aria-label="Đăng nhập">
                    <img src="{{ asset('images/figma/user.svg') }}" alt="" width="24"
                        height="24"><span>Đăng nhập</span>
                </a>
            @endauth
            <a class="header-action header-cart @if (request()->routeIs('cart.*', 'checkout.*')) is-active @endif"
                href="{{ route('cart.index') }}" aria-label="Giỏ hàng, {{ $cartQuantity }} sản phẩm" data-cart-link>
                <img src="{{ asset('images/figma/bag.svg') }}" alt="" width="24" height="24"> <span
                    class="header-cart-label">Giỏ hàng</span><span class="header-cart-count"
                    data-cart-count>{{ $cartQuantity }}</span>
            </a>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="header-logout">Đăng xuất</button>
                </form>
            @endauth
        </div>
        <button type="button" class="header-toggle" aria-expanded="false" aria-controls="header-drawer"
            data-nav-toggle><span class="header-toggle-bars" aria-hidden="true"></span><span
                class="header-toggle-label">Menu</span></button>
    </div>
</header>
