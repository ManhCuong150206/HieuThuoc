<header class="site-header">
    <div class="site-container header-main">
        <a class="header-logo" href="{{ route('home') }}" aria-label="An Tâm Pharmacy — Trang chủ">
            <img src="{{ asset('images/logo.png') }}" alt="An Tâm Pharmacy">
        </a>
        <div class="header-search" role="search">
            <img src="{{ asset('images/figma/search.svg') }}" alt="" width="24" height="24">
            <input type="search" aria-label="Tìm sản phẩm" placeholder="Tìm thuốc, sản phẩm chăm sóc sức khỏe...">
        </div>
        <div class="header-actions">
            @auth
            <a class="header-action header-account header-account--signed-in @if (request()->routeIs('account.*')) is-active @endif" href="{{ route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'account.index') }}" aria-label="Tài khoản {{ auth()->user()->username }}">
                <img src="{{ asset('images/figma/user.svg') }}" alt="" width="24" height="24"><span>{{ auth()->user()->username }}</span>
            </a>
            @else
            <a class="header-action header-account @if (request()->routeIs('login', 'register')) is-active @endif" href="{{ route('login') }}" aria-label="Đăng nhập">
                <img src="{{ asset('images/figma/user.svg') }}" alt="" width="24" height="24"><span>Đăng nhập</span>
            </a>
            @endauth
            <a class="header-action header-cart @if (request()->routeIs('cart.*', 'checkout.*')) is-active @endif" href="{{ route('cart.index') }}" aria-label="Giỏ hàng, {{ $cartQuantity }} sản phẩm">
                <img src="{{ asset('images/figma/bag.svg') }}" alt="" width="24" height="24"> <span class="header-cart-label">Giỏ hàng</span><span class="header-cart-count">{{ $cartQuantity }}</span>
            </a>
            @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="header-logout">Đăng xuất</button>
            </form>
            @endauth
        </div>
    </div>
    <nav class="header-navigation" aria-label="Điều hướng chính">
        <ul class="site-container header-menu">
            <li><a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Trang chủ</a></li>
            <li class="header-categories" data-category-menu>
                <button class="header-category-trigger @if (request()->routeIs('products.*', 'categories.show')) is-active @endif" type="button" aria-expanded="false" aria-controls="header-category-list">Danh mục </button>
                <ul class="header-category-list" id="header-category-list" hidden>
                    <li><a href="{{ route('products.index') }}">Tất cả sản phẩm</a></li>
                    @foreach ($navigationCategories as $navigationCategory)
                    <li><a href="{{ route('categories.show', $navigationCategory) }}" @if (request()->routeIs('categories.show') && (string) request()->route('category')?->id === (string) $navigationCategory->id) aria-current="page" @endif>{{ $navigationCategory->name }}</a></li>
                    @endforeach
                </ul>
            </li>
            <li><a href="{{ route('home') }}#goc-suc-khoe">Tin tức sức khỏe</a></li>
            <li><a href="{{ route('home') }}#lien-he">Liên hệ</a></li>
        </ul>
    </nav>
</header>