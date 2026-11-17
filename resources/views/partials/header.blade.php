<header class="site-header">
    <div class="utility-bar">
        <div class="site-container utility-inner">
            <p>Chăm sóc sức khỏe, bắt đầu từ sự an tâm.</p>
            <div class="utility-links">
                <a href="{{ route('account.index') }}#don-hang">Tra cứu đơn hàng</a>
                <span aria-hidden="true">•</span>
                <span>Hỗ trợ khách hàng</span>
            </div>
        </div>
    </div>
    <div class="site-container header-main">
        <a class="header-logo" href="{{ route('home') }}" aria-label="An Tâm Pharmacy — Trang chủ">
            <img src="{{ asset('images/logo.png') }}" alt="An Tâm Pharmacy">
        </a>
        <div class="header-search" role="search">
            <img src="{{ asset('images/figma/search.svg') }}" alt="" width="24" height="24">
            <input type="search" aria-label="Tìm sản phẩm" placeholder="Tìm tên thuốc, hoạt chất, triệu chứng...">
        </div>
        <div class="header-actions">
            <a class="header-action @if (request()->routeIs('login', 'account.*')) is-active @endif" href="{{ route('login') }}">
                <img src="{{ asset('images/figma/user.svg') }}" alt="" width="24" height="24"> <span>Đăng nhập</span>
            </a>
            <a class="header-action @if (request()->routeIs('cart.*', 'checkout.*')) is-active @endif" href="{{ route('cart.index') }}">
                <img src="{{ asset('images/figma/bag.svg') }}" alt="" width="24" height="24"> <span>Giỏ hàng (2)</span>
            </a>
        </div>
    </div>
    <nav class="header-navigation" aria-label="Điều hướng chính">
        <ul class="site-container header-menu">
            <li><a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Trang chủ</a></li>
            <li><a href="{{ route('products.index') }}" @if (request()->routeIs('products.*') || (request()->routeIs('categories.show') && request()->route('slug') === 'thuoc')) aria-current="{{ request()->routeIs('products.show') ? 'true' : 'page' }}" @endif>Danh mục thuốc</a></li>
            @foreach (['thuc-pham-chuc-nang' => 'Thực phẩm chức năng', 'dung-cu-y-te' => 'Dụng cụ y tế', 'cham-soc-ca-nhan' => 'Chăm sóc cá nhân'] as $slug => $label)
                <li>
                    <a href="{{ route('categories.show', $slug) }}" @if (request()->routeIs('categories.show') && request()->route('slug') === $slug) aria-current="page" @endif>{{ $label }}</a>
                </li>
            @endforeach
            <li><a href="{{ route('home') }}#goc-suc-khoe">Tin tức sức khỏe</a></li>
            <li><span>Liên hệ</span></li>
        </ul>
    </nav>
</header>
