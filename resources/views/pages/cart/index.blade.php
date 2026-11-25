@extends('layouts.app')
@section('title', 'Giỏ hàng')
@section('content')
    <div class="site-container page-stack">
        <header class="page-title">
            <x-section-title title="Giỏ hàng của bạn" :level="1" />
            <x-breadcrumb :items="[['label' => 'Giỏ hàng']]" />
        </header>
        <section class="checkout-panel" data-cart-empty @if ($items->isNotEmpty()) hidden @endif>
            <h2 tabindex="-1">Giỏ hàng của bạn đang trống</h2>
            <p>Khám phá các sản phẩm chăm sóc sức khỏe tại An Tâm.</p>
            <div><x-button :href="route('products.index')">Khám phá sản phẩm</x-button></div>
        </section>
        @if ($items->isNotEmpty())
            <div class="checkout-layout" data-cart-selection>
                <section class="checkout-panel cart-items-panel">
                    <h2>Sản phẩm trong giỏ (<span data-cart-quantity>{{ $quantity }}</span>)</h2>
                    @foreach ($items as $item)
                        @php($canCheckout = $item->product->canPurchase($item->quantity))
                        <article class="cart-row" data-cart-row="{{ $item->id }}">
                            <label class="cart-select">
                                <input type="checkbox" name="items[]" value="{{ $item->id }}" form="cart-checkout"
                                    data-cart-item data-unit-price="{{ $item->product->saleUnitPrice() }}"
                                    @checked($canCheckout && in_array($item->id, $selectedIds, true)) @disabled(!$canCheckout)>
                                <span class="sr-only">Chọn {{ $item->product->name }} để đặt hàng</span>
                            </label>
                            <a href="{{ route('products.show', $item->product_id) }}" class="cart-art">
                                <x-product-image :product="$item->product" width="88" height="88" />
                            </a>
                            <div class="cart-copy">
                                <h3><a href="{{ route('products.show', $item->product_id) }}">{{ $item->product->name }}</a></h3>
                                @if ($item->product->brand)
                                    <p>{{ $item->product->brand }}</p>
                                @endif
                                <p>Đơn giá:
                                    <del class="sale-price-original" data-cart-regular-price @if ($item->product->regularUnitPrice() === $item->product->saleUnitPrice()) hidden @endif>{{ number_format($item->product->regularUnitPrice(), 0, ',', '.') }}đ</del>
                                    <span data-cart-unit-price>{{ number_format($item->product->saleUnitPrice(), 0, ',', '.') }}đ</span>
                                </p>
                                <p data-cart-line-quantity>Số lượng: {{ $item->quantity }}</p>
                                @unless ($canCheckout)
                                    <p class="auth-field-error" data-cart-unavailable>Sản phẩm không đủ điều kiện đặt hàng. Vui lòng cập nhật hoặc
                                        xóa.</p>
                                @endunless
                            </div>
                            <div class="cart-quantity">
                                <form method="POST" action="{{ route('cart.update', $item->id) }}" class="quantity-form" data-ajax-action="cart">
                                    @csrf @method('PUT')
                                    <label class="sr-only" for="quantity-{{ $item->id }}">Số lượng
                                        {{ $item->product->name }}</label>
                                    <input id="quantity-{{ $item->id }}" name="quantity" type="number" min="1"
                                        max="{{ max(1, $item->product->maxCartQuantity()) }}" value="{{ $item->quantity }}" required>
                                </form>
                                <form method="POST" action="{{ route('cart.destroy', $item->id) }}" data-ajax-action="cart">@csrf
                                    @method('DELETE')<button class="text-action" type="submit">Xóa</button></form>
                            </div>
                            <div class="cart-line-total">
                                <span>Thành tiền</span>
                                <p class="cart-price">{{ number_format($item->subtotal(), 0, ',', '.') }}đ</p>
                            </div>
                        </article>
                    @endforeach
                    <form method="POST" action="{{ route('cart.clear') }}" data-ajax-action="cart">@csrf @method('DELETE')<x-button type="submit"
                            variant="outline">Xóa toàn bộ giỏ</x-button></form>
                </section>
                <aside class="checkout-panel checkout-summary">
                    <h2>Tóm tắt giỏ hàng</h2>
                    <dl class="summary-list" data-cart-summary hidden>
                        <div>
                            <dt>Số lượng đã chọn</dt>
                            <dd data-selected-quantity>0</dd>
                        </div>
                    </dl>
                    <dl class="summary-total" data-cart-summary hidden>
                        <dt>Tạm tính</dt>
                        <dd data-selected-total aria-live="polite">0đ</dd>
                    </dl>
                    <p class="checkout-note" data-selection-hint>Tích vào sản phẩm bạn muốn mua.</p>
                    <p class="checkout-note" data-selection-fees hidden>Phí giao hàng và ưu đãi được tính tại bước thanh toán.</p>
                    <form id="cart-checkout" method="POST" action="{{ route('checkout.select') }}">
                        @csrf
                        <x-button type="submit" class="full-width">Tiến hành đặt hàng</x-button>
                    </form>
                    <x-button variant="outline" :href="route('products.index')">Tiếp tục mua sắm</x-button>
                </aside>
            </div>
        @endif
    </div>
@endsection
