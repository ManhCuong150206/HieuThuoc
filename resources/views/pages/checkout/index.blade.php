@extends('layouts.app')
@section('title', 'Giỏ hàng & thanh toán')
@section('content')
<div class="site-container page-stack">
    <x-breadcrumb :items="[['label' => 'Giỏ hàng', 'href' => route('cart.index')], ['label' => 'Thanh toán']]" />
    <x-section-title eyebrow="CHỈ CÒN MỘT BƯỚC" title="Hoàn tất đơn hàng" :level="1" />
    <nav aria-label="Các bước đặt hàng" class="checkout-steps">
        <a href="{{ route('cart.index') }}" @if (request()->routeIs('cart.index')) aria-current="step" @endif>✓ &nbsp;Giỏ hàng</a>
        <a href="{{ route('checkout.index') }}#thong-tin-giao-hang" @if (request()->routeIs('checkout.index')) aria-current="step" @endif>02 &nbsp;Thông tin &amp; thanh toán</a>
        <span>03 &nbsp;Xác nhận đơn hàng</span>
    </nav>
    <div class="checkout-layout">
        <div class="checkout-forms">
            <section class="checkout-panel">
                <h2>Giỏ hàng của bạn (2 sản phẩm)</h2>
                @foreach ($items as $item)
                    <article class="cart-row">
                        <a href="{{ route('products.show', $item['product']['slug']) }}" class="cart-art" aria-label="{{ $item['product']['name'] }}">
                            <x-icon :name="$item['image']" />
                        </a>
                        <div class="cart-copy">
                            <h3><a href="{{ route('products.show', $item['product']['slug']) }}">{{ $item['product']['name'] }}</a></h3>
                            <p>{{ $item['product']['package'] }} • {{ $item['product']['type'] }}</p>
                        </div>
                        <div class="cart-quantity">
                            <x-quantity :label="'Số lượng '.$item['product']['name']" />
                            <button type="button">Xóa</button>
                        </div>
                        <p class="cart-price">{{ $item['product']['price'] }}</p>
                    </article>
                @endforeach
            </section>
            <section class="checkout-panel" id="thong-tin-giao-hang">
                <h2>1. Thông tin giao hàng</h2>
                <div class="field-pair">
                    <x-input id="delivery-name" label="Họ và tên *" placeholder="Nguyễn Minh Anh" />
                    <x-input id="delivery-phone" label="Số điện thoại *" type="tel" placeholder="09xx xxx xxx" />
                </div>
                <x-input id="delivery-email" label="Email nhận xác nhận *" type="email" placeholder="minhanh@example.com" />
                <div class="field-pair">
                    <x-input id="delivery-city" label="Tỉnh / Thành phố *" placeholder="Chọn tỉnh / thành phố" />
                    <x-input id="delivery-ward" label="Phường / Xã *" placeholder="Chọn phường / xã" />
                </div>
                <x-input id="delivery-address" label="Địa chỉ cụ thể *" placeholder="Số nhà, tên đường..." />
                <x-input id="delivery-note" label="Ghi chú cho đơn hàng" placeholder="Ví dụ gọi trước khi giao hàng" />
            </section>
            <section class="checkout-panel" aria-labelledby="payments-title">
                <h2 id="payments-title">2. Phương thức thanh toán</h2>
                <div class="payment-options" role="group" aria-labelledby="payments-title">
                    @foreach ($payments as [$id, $method, $copy])
                        <label class="payment-option">
                            <input type="radio" name="payment" value="{{ $id }}" @checked($loop->first)>
                            <span><strong>{{ $method }}</strong><small>{{ $copy }}</small></span>
                        </label>
                    @endforeach
                </div>
            </section>
        </div>
        <aside class="checkout-panel checkout-summary">
            <h2>Tóm tắt đơn hàng</h2>
            <x-input id="coupon" label="Mã ưu đãi" placeholder="Nhập mã ưu đãi" />
            <x-button variant="soft" class="full-width">Áp dụng</x-button>
            <dl class="summary-list">
                @foreach ($summary as $label => $value)<div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>@endforeach
            </dl>
            <dl class="summary-total"><dt>Tổng thanh toán</dt><dd>{{ $order['total'] }}</dd></dl>
            <p class="checkout-note">Đã bao gồm VAT (nếu có).</p>
            <label class="checkout-note check-label"><input type="checkbox" checked> Tôi đồng ý với điều khoản mua hàng và chính sách bảo mật của An Tâm.</label>
            <x-button class="full-width" :href="route('checkout.success')">Đặt hàng • {{ $order['total'] }}</x-button>
            <p class="checkout-note">Thông tin cá nhân được bảo vệ.</p>
            <x-button variant="outline" class="full-width" :href="route('products.index')">← Tiếp tục mua sắm</x-button>
        </aside>
    </div>
</div>
@endsection
