@extends('layouts.app')
@section('title', $accountPageTitle)

@section('content')
    <div class="site-container page-stack">
        <header class="page-title">
            <x-section-title :title="$accountPageTitle" :level="1" />
            <x-breadcrumb :items="[['label' => $accountPageTitle]]" />
        </header>
        <div class="account-layout">
            <aside class="account-nav">
                <div class="account-avatar"><x-icon name="account-user" :size="40" /></div>
                <div class="account-identity">
                    <strong>{{ $profile?->full_name ?: $account->username }}</strong>
                    <span>{{ $profile?->email ?: 'Chưa cập nhật thông tin cá nhân' }}</span>
                </div>
                <nav class="account-links" aria-label="Điều hướng tài khoản">
                    <x-button :variant="request()->routeIs('account.index') ? 'primary' : 'outline'"
                        :href="route('account.index')" :aria-current="request()->routeIs('account.index') ? 'page' : null">Thông tin cá nhân</x-button>
                    <x-button :variant="request()->routeIs('account.orders') ? 'primary' : 'outline'"
                        :href="route('account.orders')" :aria-current="request()->routeIs('account.orders') ? 'page' : null">Đơn hàng của tôi</x-button>
                </nav>
            </aside>
            <div class="account-content">
                @yield('account-content')
            </div>
        </div>
    </div>
@endsection
