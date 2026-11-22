@extends('layouts.app')
@section('title', 'Thanh toán')
@section('content')
<div class="site-container page-stack">
    <x-breadcrumb :items="[['label' => 'Giỏ hàng', 'href' => route('cart.index')], ['label' => 'Thanh toán']]" />
    <x-section-title title="Thông tin đặt hàng" description="Kiểm tra sản phẩm và địa chỉ trước khi xác nhận." :level="1" />
    <form method="POST" action="{{ route('checkout.store') }}" class="checkout-layout" data-checkout data-subtotal="{{ $subtotal }}" data-shipping="{{ $shippingFee }}">
        @csrf
        <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
        <div class="checkout-forms">
            <section class="checkout-panel">
                <h2>Sản phẩm ({{ $items->sum('quantity') }})</h2>
                @foreach ($items as $item)
                    <div class="checkout-item"><span>{{ $item->name }} × {{ $item->quantity }}</span><strong>{{ number_format(round($item->price) * $item->quantity, 0, ',', '.') }}đ</strong></div>
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
            <div class="ui-field">
                <label for="promotion">Ưu đãi hiện có</label>
                <select id="promotion" name="promotion_id" data-promotion>
                    <option value="" data-percent="0">Không áp dụng</option>
                    @foreach ($promotions as $promotion)
                        <option value="{{ $promotion->id }}" data-percent="{{ $promotion->discount_percent }}" @selected((string) old('promotion_id') === (string) $promotion->id)>{{ $promotion->name }} — giảm {{ $promotion->discount_percent }}%</option>
                    @endforeach
                </select>
            </div>
            <dl class="summary-list">
                <div><dt>Tạm tính</dt><dd>{{ number_format($subtotal, 0, ',', '.') }}đ</dd></div>
                <div><dt>Phí vận chuyển</dt><dd>{{ number_format($shippingFee, 0, ',', '.') }}đ</dd></div>
                <div><dt>Giảm giá</dt><dd data-discount>0đ</dd></div>
            </dl>
            <dl class="summary-total"><dt>Tổng thanh toán</dt><dd data-total>{{ number_format($subtotal + $shippingFee, 0, ',', '.') }}đ</dd></dl>
            <label class="check-label checkout-note"><input type="checkbox" name="terms" value="1" required @checked(old('terms'))> Tôi đồng ý với điều khoản mua hàng và chính sách bảo mật.</label>
            <x-button type="submit" class="full-width" data-place-order>Xác nhận đặt hàng</x-button>
            <p class="checkout-note">Ưu đãi, giá và tồn kho được kiểm tra lại khi xác nhận.</p>
        </aside>
    </form>
</div>
@endsection
