@extends('layouts.app')
@section('title', 'Tài khoản của tôi')
@section('content')
<div class="site-container page-stack">
    <x-breadcrumb :items="[['label' => 'Tài khoản của tôi']]" />
    <x-section-title eyebrow="TÀI KHOẢN CỦA TÔI" :title="'Xin chào, '.$profile['firstName']" description="Quản lý thông tin và theo dõi đơn hàng của bạn." :level="1" />
    <div class="account-layout">
        <aside class="account-nav" aria-label="Điều hướng tài khoản">
            <div class="account-avatar"><x-icon name="account-user" :size="40" /></div>
            <div class="account-identity"><strong>{{ $profile['name'] }}</strong><span>{{ $profile['email'] }}</span></div>
            <x-button variant="soft" :href="route('account.index').'#thong-tin-ca-nhan'">Thông tin cá nhân</x-button>
            <x-button variant="outline" :href="route('account.index').'#don-hang'">Đơn hàng của tôi</x-button>
            <x-button variant="outline">Sổ địa chỉ</x-button>
            <x-button variant="outline">Sản phẩm yêu thích</x-button>
            <x-button variant="outline">Đổi mật khẩu</x-button>
            <x-button variant="outline">Đăng xuất</x-button>
        </aside>
        <div class="account-content">
            <section class="account-panel" id="thong-tin-ca-nhan">
                <h2>Thông tin cá nhân</h2>
                <div class="field-pair profile-name">
                    <x-input id="profile-name" label="Họ và tên" :value="$profile['name']" />
                    <x-input id="profile-phone" label="Số điện thoại" type="tel" :value="$profile['phone']" />
                </div>
                <div class="profile-email">
                    <x-input id="profile-email" label="Email" type="email" :value="$profile['email']" />
                    <x-button>Lưu thay đổi</x-button>
                </div>
            </section>
            <section class="account-panel" id="don-hang">
                <h2>Đơn hàng của tôi</h2>
                <div class="order-filters">
                    <span class="is-active">Tất cả (3)</span><span>Chờ xác nhận (1)</span><span>Đang giao (1)</span><span>Đã giao (1)</span>
                </div>
                @foreach ($orders as $order)<x-order-card :order="$order" />@endforeach
            </section>
        </div>
    </div>
</div>
@endsection
