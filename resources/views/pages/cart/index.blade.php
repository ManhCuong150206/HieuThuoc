@extends('layouts.app')
@section('title', 'Giỏ hàng')
@section('content')
    <div class="site-container page-stack">
        <header class="page-title">
            <x-section-title title="Giỏ hàng của bạn" :level="1" />
            <x-breadcrumb :items="[['label' => 'Giỏ hàng']]" />
        </header>
        @if ($items->isEmpty())
            <section class="checkout-panel">
                <h2>Giỏ hàng của bạn đang trống</h2>
                <p>Khám phá các sản phẩm chăm sóc sức khỏe tại An Tâm.</p>
                <div><x-button :href="route('products.index')">Khám phá sản phẩm</x-button></div>
            </section>
        @else
            <div class="checkout-layout">
                <section class="checkout-panel">
                    <h2>Sản phẩm trong giỏ ({{ $quantity }})</h2>
                    @foreach ($items as $item)
                        @php
                            // same image rule as every other page: Product::imageSrc() (local file by name, else image_url)
                            $cartProduct = new \App\Models\Product();
                            $cartProduct->forceFill(['name' => $item->name, 'image_url' => $item->image_url]);
                        @endphp
                        <article class="cart-row">
                            <a href="{{ route('products.show', $item->product_id) }}" class="cart-art">
                                <x-product-image :product="$cartProduct" width="72" height="72" />
                            </a>
                            <div class="cart-copy">
                                <h3><a href="{{ route('products.show', $item->product_id) }}">{{ $item->name }}</a></h3>
                                @if ($item->brand)
                                    <p>{{ $item->brand }}</p>
                                @endif
                                <p>Đơn giá: {{ number_format($item->price, 0, ',', '.') }}đ</p>
                                <p>Số lượng: {{ $item->quantity }}</p>
                                @if (
                                    !$item->is_active ||
                                        $item->price <= 0 ||
                                        $item->stock < $item->quantity ||
                                        ($item->expiry_date && \Illuminate\Support\Carbon::parse($item->expiry_date)->lt(today())))
                                    <p class="auth-field-error">Sản phẩm không đủ điều kiện đặt hàng. Vui lòng cập nhật hoặc
                                        xóa.</p>
                                @endif
                            </div>
                            <div class="cart-quantity">
                                <form method="POST" action="{{ route('cart.update', $item->id) }}" class="quantity-form">
                                    @csrf @method('PUT')
                                    <label class="sr-only" for="quantity-{{ $item->id }}">Số lượng
                                        {{ $item->name }}</label>
                                    <input id="quantity-{{ $item->id }}" name="quantity" type="number" min="1"
                                        max="999" value="{{ $item->quantity }}" required>
                                    <button type="submit" class="text-action">Cập nhật</button>
                                </form>
                                <form method="POST" action="{{ route('cart.destroy', $item->id) }}">@csrf
                                    @method('DELETE')<button class="text-action" type="submit">Xóa</button></form>
                            </div>
                            <p class="cart-price">{{ number_format(round($item->price) * $item->quantity, 0, ',', '.') }}đ
                            </p>
                        </article>
                    @endforeach
                    <form method="POST" action="{{ route('cart.clear') }}">@csrf @method('DELETE')<x-button type="submit"
                            variant="outline">Xóa toàn bộ giỏ</x-button></form>
                </section>
                <aside class="checkout-panel checkout-summary">
                    <h2>Tóm tắt giỏ hàng</h2>
                    <dl class="summary-list">
                        <div>
                            <dt>Số lượng sản phẩm</dt>
                            <dd>{{ $quantity }}</dd>
                        </div>
                    </dl>
                    <dl class="summary-total">
                        <dt>Tạm tính</dt>
                        <dd>{{ number_format($subtotal, 0, ',', '.') }}đ</dd>
                    </dl>
                    <p class="checkout-note">Phí giao hàng và ưu đãi được tính tại bước thanh toán.</p>
                    <x-button :href="route('checkout.index')">Tiến hành đặt hàng</x-button>
                    <x-button variant="outline" :href="route('products.index')">Tiếp tục mua sắm</x-button>
                </aside>
            </div>
        @endif
    </div>
@endsection
