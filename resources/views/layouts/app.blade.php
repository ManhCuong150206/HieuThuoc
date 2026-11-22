<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Nhà thuốc trực tuyến') | An Tâm Pharmacy</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="@yield('body-class', 'storefront')">
    @unless (request()->routeIs('admin.*'))
        @include('partials.header')
    @endunless
    <main id="main-content" class="@yield('main-class')">
        @if (session('status'))
            <div class="site-container feedback-message auth-success" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="site-container feedback-message feedback-error" role="alert">
                <p>Vui lòng kiểm tra lại thông tin:</p>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </main>
    @unless (request()->routeIs('admin.*'))
        @include('partials.footer')
    @endunless
    @stack('scripts')
    <div class="ajax-feedback" data-ajax-feedback data-login-url="{{ route('login') }}" data-cart-url="{{ route('cart.index') }}" data-favorites-url="{{ route('favorites.index') }}" hidden>
        <div>
            <p role="status" aria-live="polite" aria-atomic="true" data-ajax-message></p>
            <a class="text-action" data-ajax-link hidden></a>
        </div>
        <button type="button" class="ajax-feedback-close" aria-label="Đóng thông báo" data-ajax-dismiss>×</button>
    </div>
</body>
</html>
