@extends('layouts.app')
@section('title', 'Thông tin đơn hàng')
@section('content')
<div class="site-container confirmation-area">
    @php($cancelled = $order->status === 'CANCELLED')
    <header class="confirmation-card">
        @unless ($cancelled)
            <span class="success-mark" aria-hidden="true"><x-icon name="success-check" :size="28" /></span>
        @endunless
        <div>
            <h1>{{ $cancelled ? 'Đơn hàng đã hủy' : 'Đặt hàng thành công!' }}</h1>
            <p class="confirmation-code">Mã đơn hàng <strong>{{ $order->order_code }}</strong></p>
        </div>
        <x-badge :variant="$cancelled ? 'muted' : 'pale'">{{ $statusLabels[$order->status] ?? $order->status }}</x-badge>
    </header>
    <div class="checkout-layout">
        <section class="checkout-panel confirmation-products" aria-labelledby="confirmation-products-title">
            <h2 id="confirmation-products-title">Sản phẩm đã đặt <span>{{ $order->items->sum('quantity') }}</span></h2>
            <ul class="account-order-items">
                @foreach ($order->items as $item)
                    <li>
                        <x-order-product-line :item="$item"
                            :href="$item->product?->is_active ? route('products.show', $item->product_id) : null" />
                    </li>
                @endforeach
            </ul>
        </section>
        <div class="checkout-forms">
            <section class="checkout-panel" aria-labelledby="confirmation-delivery-title">
                <h2 id="confirmation-delivery-title">Giao hàng</h2>
                <dl class="confirmation-details">
                    <div><dt>Người nhận</dt><dd>{{ $order->recipient_name }}</dd></div>
                    <div><dt>Số điện thoại</dt><dd>{{ $order->recipient_phone }}</dd></div>
                    <div><dt>Địa chỉ</dt><dd>{{ $order->shipping_address }}</dd></div>
                </dl>
            </section>
            <section class="checkout-panel" aria-labelledby="confirmation-payment-title">
                <h2 id="confirmation-payment-title">Thanh toán</h2>
                <dl class="confirmation-details">
                    <div><dt>Tiền mặt</dt><dd>{{ $order->payments->contains('status', 'SUCCESS') ? 'Thành công' : ($cancelled ? 'Đã hủy' : 'Chưa hoàn tất') }}</dd></div>
                    <div><dt>Tạm tính</dt><dd>{{ number_format($order->subtotal, 0, ',', '.') }}đ</dd></div>
                    <div><dt>Phí vận chuyển</dt><dd>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</dd></div>
                    @if ($order->discount_amount > 0)
                        <div><dt>Giảm giá</dt><dd>−{{ number_format($order->discount_amount, 0, ',', '.') }}đ</dd></div>
                    @endif
                    <div class="summary-total"><dt>Tổng thanh toán</dt><dd>{{ number_format($order->total_amount, 0, ',', '.') }}đ</dd></div>
                </dl>
            </section>
        </div>
    </div>
    <div class="confirmation-actions">
        <x-button :href="route('account.orders')">Xem đơn hàng</x-button>
        <x-button variant="outline" :href="route('products.index')">Tiếp tục mua sắm</x-button>
    </div>
</div>
@endsection
