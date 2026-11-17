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
        @yield('content')
    </main>
    @unless (request()->routeIs('admin.*'))
        @include('partials.footer')
    @endunless
    @stack('scripts')
</body>
</html>
