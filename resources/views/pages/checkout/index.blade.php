@extends('layouts.app')
@section('title', 'Thanh toán')
@section('content')
<div class="site-container page-stack">
    <header class="page-title">
        <x-section-title title="Thông tin đặt hàng" description="Kiểm tra sản phẩm và địa chỉ trước khi xác nhận." :level="1" />
        <x-breadcrumb :items="[['label' => 'Giỏ hàng', 'href' => route('cart.index')], ['label' => 'Thanh toán']]" />
    </header>
    <form method="POST" action="{{ route('checkout.store') }}" class="checkout-layout" data-checkout data-subtotal="{{ $subtotal }}" data-shipping="{{ $shippingFee }}">
        @csrf
        <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
        <div class="checkout-forms">
            <section class="checkout-panel">
                <h2>Sản phẩm ({{ $items->sum('quantity') }})</h2>
                @foreach ($items as $item)
                    <div class="checkout-item">
                        <span>{{ $item->product->name }} × {{ $item->quantity }}</span>
                        <strong><x-sale-price :price="$item->subtotal()" :regular-price="$item->regularSubtotal()" /></strong>
                    </div>
                @endforeach
                <a class="text-action" href="{{ route('cart.index') }}">Sửa giỏ hàng</a>
            </section>
            <section class="checkout-panel" id="thong-tin-giao-hang">
                <h2>Thông tin giao hàng</h2>
                <div class="field-pair">
                    <x-input id="recipient-name" name="recipient_name" label="Họ và tên người nhận" :value="old('recipient_name', $profile->full_name)" autocomplete="name" required :show-error="true" />
                    <x-input id="recipient-phone" name="recipient_phone" label="Số điện thoại" type="tel" :value="old('recipient_phone', $profile->phone)" autocomplete="tel" required :show-error="true" />
                </div>
                <x-input id="shipping-address" name="shipping_address" label="Địa chỉ giao hàng đầy đủ" :value="old('shipping_address', $profile->address)" placeholder="Số nhà, đường, phường/xã, tỉnh/thành phố" autocomplete="street-address" required :show-error="true" />
            </section>
            <section class="checkout-panel" aria-labelledby="payments-title">
                <h2 id="payments-title">Phương thức thanh toán</h2>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="COD" checked required>
                        <span><strong>Thanh toán tiền mặt</strong><small>Đơn và thanh toán được tự xác nhận thành công sau khi đặt hàng.</small></span>
                    </label>
                    <label class="payment-option payment-unavailable">
                        <input type="radio" name="payment_method" value="QR" disabled>
                        <span><strong>Thanh toán mã QR</strong><small>Đang bổ sung</small></span>
                    </label>
                </div>
            </section>
        </div>
        <aside class="checkout-panel checkout-summary">
            <h2>Tóm tắt đơn hàng</h2>
            @php
                $selectedPromotion = old('promotion_id');
                $selectedDiscount = is_numeric($selectedPromotion) ? ($promotionDiscounts[(int) $selectedPromotion] ?? 0) : 0;
            @endphp
            @if ($promotions->isNotEmpty())
            <div class="ui-field">
                <label for="promotion">Ưu đãi cho cả đơn</label>
                <select id="promotion" name="promotion_id" data-promotion>
                    <option value="" data-amount="0">Không áp dụng</option>
                    @foreach ($promotions as $promotion)
                        <option value="{{ $promotion->id }}" data-amount="{{ $promotionDiscounts[$promotion->id] }}" @selected(is_numeric($selectedPromotion) && (int) $selectedPromotion === $promotion->id)>{{ $promotion->name }} — giảm {{ $promotion->discount_percent }}%</option>
                    @endforeach
                </select>
            </div>
            @endif
            <dl class="summary-list">
                <div><dt>Tạm tính</dt><dd>{{ number_format($subtotal, 0, ',', '.') }}đ</dd></div>
                @if ($productSavings > 0)
                    <div class="checkout-note"><dt>Đã giảm trên sản phẩm</dt><dd>{{ number_format($productSavings, 0, ',', '.') }}đ</dd></div>
                @endif
                <div><dt>Phí vận chuyển</dt><dd>{{ number_format($shippingFee, 0, ',', '.') }}đ</dd></div>
                @if ($promotions->isNotEmpty())
                    <div><dt>Giảm thêm cho đơn</dt><dd data-discount>{{ number_format($selectedDiscount, 0, ',', '.') }}đ</dd></div>
                @endif
            </dl>
            <dl class="summary-total"><dt>Tổng thanh toán</dt><dd data-total>{{ number_format($subtotal - $selectedDiscount + $shippingFee, 0, ',', '.') }}đ</dd></dl>
            <label class="check-label checkout-note"><input type="checkbox" name="terms" value="1" required @checked(old('terms'))> Tôi đồng ý với điều khoản mua hàng và chính sách bảo mật.</label>
            <x-button type="submit" class="full-width" data-place-order>Xác nhận đặt hàng</x-button>
            <p class="checkout-note">Ưu đãi, giá và tồn kho được kiểm tra lại khi xác nhận.</p>
        </aside>
    </form>
</div>
@endsection
