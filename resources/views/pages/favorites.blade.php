@extends('layouts.app')
@section('title', 'Sản phẩm yêu thích')
@section('content')
<div class="site-container page-stack">
    <header class="page-title">
        <x-section-title title="Sản phẩm yêu thích" :level="1" />
    </header>
    @if ($products && $products->isNotEmpty())
        <div class="product-grid">
            @foreach ($products as $product)
                <div class="saved-product">
                    <x-product-card :product="$product" />
                    <form method="POST" action="{{ route('favorites.destroy', $product) }}">@csrf @method('DELETE')<x-button type="submit" variant="outline" class="full-width">Bỏ yêu thích</x-button></form>
                </div>
            @endforeach
        </div>
        <x-pagination :paginator="$products" />
    @else
        <section class="checkout-panel"><p>Bạn chưa lưu sản phẩm nào.</p><x-button :href="route('products.index')">Khám phá sản phẩm</x-button></section>
    @endif
</div>
@endsection
