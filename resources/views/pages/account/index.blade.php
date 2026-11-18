@extends('layouts.app')
@section('title', 'Tài khoản của tôi')
@section('content')
<div class="site-container page-stack">
    <x-breadcrumb :items="[['label' => 'Tài khoản của tôi']]" />
    <x-section-title eyebrow="TÀI KHOẢN CỦA TÔI" :title="'Xin chào, '.($profile?->full_name ?: $account->username)" description="Quản lý thông tin và theo dõi đơn hàng của bạn." :level="1" />
    <div class="account-layout">
        <aside class="account-nav" aria-label="Điều hướng tài khoản">
            <div class="account-avatar"><x-icon name="account-user" :size="40" /></div>
            <div class="account-identity"><strong>{{ $profile?->full_name ?: $account->username }}</strong><span>{{ $profile?->email ?: 'Chưa cập nhật thông tin cá nhân' }}</span></div>
            <x-button variant="soft" :href="route('account.index').'#thong-tin-ca-nhan'">Thông tin cá nhân</x-button>
            <x-button variant="outline" :href="route('account.index').'#don-hang'">Đơn hàng của tôi</x-button>
            <x-button variant="outline" :href="route('account.index').'#profile-address'">Địa chỉ</x-button>
        </aside>
        <div class="account-content">
            <section class="account-panel" id="thong-tin-ca-nhan">
                <h2>Thông tin cá nhân</h2>
                @if (session('status'))<p class="auth-success" role="status">{{ session('status') }}</p>@endif
                <form class="profile-form" method="POST" action="{{ route('account.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="field-pair profile-name">
                        <x-input id="profile-name" name="full_name" label="Họ và tên" :value="old('full_name', $profile?->full_name)" autocomplete="name" required :show-error="true" />
                        <x-input id="profile-phone" name="phone" label="Số điện thoại" type="tel" :value="old('phone', $profile?->phone)" autocomplete="tel" required :show-error="true" />
                    </div>
                    <x-input id="profile-email" name="email" label="Email" type="email" :value="old('email', $profile?->email)" autocomplete="email" required :show-error="true" />
                    <x-input id="profile-address" name="address" label="Địa chỉ" :value="old('address', $profile?->address)" autocomplete="street-address" placeholder="Nhập địa chỉ của bạn" :show-error="true" />
                    <div class="profile-actions"><x-button type="submit">Lưu thay đổi</x-button></div>
                </form>
            </section>
            <section class="account-panel" id="don-hang">
                <h2>Đơn hàng của tôi</h2>
                <p>Tất cả ({{ $orders->total() }})</p>
                @forelse ($orders as $order)
                    <x-order-card :order="$order" :items="$orderItems->get($order->id, collect())" :status-label="$statusLabels[strtoupper($order->status)] ?? $order->status" />
                @empty
                    <p>Bạn chưa có đơn hàng nào.</p>
                @endforelse
                @if ($orders->hasPages())
                    <nav class="profile-actions" aria-label="Các trang đơn hàng">
                        @if ($orders->previousPageUrl())<x-button variant="outline" :href="$orders->previousPageUrl()">Trang trước</x-button>@endif
                        <span>Trang {{ $orders->currentPage() }} / {{ $orders->lastPage() }}</span>
                        @if ($orders->hasMorePages())<x-button variant="outline" :href="$orders->nextPageUrl()">Trang sau</x-button>@endif
                    </nav>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
