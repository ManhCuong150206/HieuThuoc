@extends('layouts.app')
@section('body-class', 'admin')
@section('content')
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-branding"><a class="admin-brand-link" href="{{ route('admin.dashboard') }}">An Tâm Pharmacy</a></div>
        <nav aria-label="Điều hướng quản trị"><ul>
            <li><a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Tổng quan</a></li>
            <li><a href="{{ route('admin.orders.index') }}" @if(request()->routeIs('admin.orders.*')) aria-current="page" @endif>Đơn hàng</a></li>
            @foreach (\App\Http\Controllers\AdminResourceController::RESOURCES as $key => [$class, $label])
                <li><a href="{{ route('admin.resources.index', $key) }}" @if(request()->route('resource') === $key) aria-current="page" @endif>{{ $label }}</a></li>
            @endforeach
            <li><a href="{{ route('admin.customers.index') }}" @if(request()->routeIs('admin.customers.*')) aria-current="page" @endif>Khách hàng</a></li>
            <li><a href="{{ route('admin.reviews.index') }}" @if(request()->routeIs('admin.reviews.*')) aria-current="page" @endif>Đánh giá</a></li>
            <li><a href="{{ route('admin.report') }}">Xuất báo cáo CSV</a></li>
            <li><a href="{{ route('home') }}">Xem cửa hàng</a></li>
        </ul></nav>
        <form method="POST" action="{{ route('logout') }}">@csrf<x-button type="submit" variant="outline">Đăng xuất</x-button></form>
    </aside>
    <div class="admin-main">@yield('admin-content')</div>
</div>
@endsection
