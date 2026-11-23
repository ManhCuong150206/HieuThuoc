<!doctype html>
<html lang="vi" data-type="product" data-radius="soft" data-buttons="solid" data-motion>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Nhà thuốc trực tuyến') | An Tâm Pharmacy</title>
    <meta name="description" content="@yield('description', 'An Tâm Pharmacy bán thuốc không kê đơn, vitamin và đồ chăm sóc cá nhân trực tuyến, giao tận nhà với phí 25.000đ mỗi đơn.')">
    <meta name="page-type" content="product">
    <meta name="site-kind" content="other">
    <meta name="theme-color" content="#f5f3ee">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Nhà thuốc trực tuyến') | An Tâm Pharmacy">
    <meta property="og:description" content="@yield('description', 'An Tâm Pharmacy bán thuốc không kê đơn, vitamin và đồ chăm sóc cá nhân trực tuyến, giao tận nhà với phí 25.000đ mỗi đơn.')">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    {{-- <link rel="canonical"> waits for the shop's own domain --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/motion/motion.css') }}">
    @stack('styles')
    <script>
        document.documentElement.classList.add('m-enter', 'mo-js');
        setTimeout(() => document.documentElement.classList.add('m-enter-done'), 700);
        setTimeout(() => document.documentElement.classList.add('mo-safe'), 2500)
    </script>
</head>

<body class="@yield('body-class', 'storefront')">
    {{-- deal: kind product (online pharmacy) · palette from the logo green on paper · Bricolage Grotesque + Be Vietnam Pro · radius soft · hero product · nav-mega · footer-dark · close-word head row · rhythm brand-band · buttons solid · moment picture-develops --}}
    @unless (request()->routeIs('admin.*'))
        @include('partials.header')
    @endunless
    <main id="main-content" class="@yield('main-class')" tabindex="-1">
        @if (session('status'))
            <div class="site-container feedback-message auth-success" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="site-container feedback-message feedback-error" role="alert">
                <p>Vui lòng kiểm tra lại thông tin:</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    @unless (request()->routeIs('admin.*'))
        @include('partials.footer')
    @endunless
    @stack('scripts')
    <div class="ajax-feedback" data-ajax-feedback data-login-url="{{ route('login') }}"
        data-cart-url="{{ route('cart.index') }}" data-favorites-url="{{ route('favorites.index') }}" hidden>
        <div>
            <p role="status" aria-live="polite" aria-atomic="true" data-ajax-message></p>
            <a class="text-action" data-ajax-link hidden></a>
        </div>
        <button type="button" class="ajax-feedback-close" aria-label="Đóng thông báo" data-ajax-dismiss>×</button>
    </div>
    <script type="module" src="{{ asset('assets/motion/motion.js') }}"></script>
    <script type="module" src="{{ asset('assets/moments/moments.js') }}"></script>
</body>

</html>
