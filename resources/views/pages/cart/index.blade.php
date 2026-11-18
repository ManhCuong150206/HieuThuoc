@extends('layouts.app')
@section('title', 'Giỏ hàng')
@section('content')
<div class="site-container page-stack">
    <x-breadcrumb :items="[['label' => 'Giỏ hàng']]" />
    <x-section-title title="Giỏ hàng của bạn" :level="1" />
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
                    <article class="cart-row">
                        <div class="cart-art">
                            @if ($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" width="72" height="72" style="max-height: 72px; object-fit: contain;">
                            @else
                                <x-icon name="admin-bag" :size="32" />
                            @endif
                        </div>
                        <div class="cart-copy">
                            <h3>{{ $item->name }}</h3>
                            @if ($item->brand)<p>{{ $item->brand }}</p>@endif
                            <p>Đơn giá: {{ number_format($item->price, 0, ',', '.') }}đ</p>
                        </div>
                        <p class="cart-quantity">Số lượng: {{ $item->quantity }}</p>
                        <p class="cart-price">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</p>
                    </article>
                @endforeach
            </section>
            <aside class="checkout-panel checkout-summary">
                <h2>Tóm tắt giỏ hàng</h2>
                <dl class="summary-list"><div><dt>Số lượng sản phẩm</dt><dd>{{ $quantity }}</dd></div></dl>
                <dl class="summary-total"><dt>Tạm tính</dt><dd>{{ number_format($subtotal, 0, ',', '.') }}đ</dd></dl>
                <x-button variant="outline" :href="route('products.index')">Tiếp tục mua sắm</x-button>
            </aside>
        </div>
    @endif
</div>
@endsection
