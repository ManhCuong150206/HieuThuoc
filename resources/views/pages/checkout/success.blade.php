@extends('layouts.app')
@section('title', 'Thông tin đơn hàng')
@section('content')
<div class="site-container confirmation-area">
    <section class="confirmation-card">
        <div class="success-mark"><x-icon name="success-check" :size="36" /></div>
        <h1>{{ $order->status === 'CANCELLED' ? 'Đơn hàng đã hủy' : 'Đặt hàng thành công!' }}</h1>
        <p class="success-message">Đơn hàng của bạn đã được lưu. Bạn có thể theo dõi trong tài khoản.</p>
        <x-badge>{{ $statusLabels[$order->status] ?? $order->status }}</x-badge>
        <dl class="confirmation-details">
            <div><dt>Mã đơn hàng</dt><dd>{{ $order->order_code }}</dd></div>
            <div><dt>Thanh toán</dt><dd>Tiền mặt — {{ $order->payments->contains('status', 'SUCCESS') ? 'Thành công' : 'Đã hủy / chưa hoàn tất' }}</dd></div>
            <div><dt>Người nhận</dt><dd>{{ $order->recipient_name }} · {{ $order->recipient_phone }}</dd></div>
            <div><dt>Địa chỉ</dt><dd>{{ $order->shipping_address }}</dd></div>
            <div><dt>Tạm tính</dt><dd>{{ number_format($order->subtotal, 0, ',', '.') }}đ</dd></div>
            <div><dt>Giảm giá</dt><dd>{{ number_format($order->discount_amount, 0, ',', '.') }}đ</dd></div>
            <div><dt>Phí vận chuyển</dt><dd>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</dd></div>
            <div><dt>Tổng thanh toán</dt><dd>{{ number_format($order->total_amount, 0, ',', '.') }}đ</dd></div>
        </dl>
        <ul class="account-order-items">@foreach ($order->items as $item)<li>{{ $item->product_name }} × {{ $item->quantity }} — {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ</li>@endforeach</ul>
        <div class="confirmation-actions">
            <x-button :href="route('account.index').'#don-hang'">Xem đơn hàng</x-button>
            <x-button variant="outline" :href="route('products.index')">Tiếp tục mua sắm</x-button>
        </div>
    </section>
</div>
@endsection
