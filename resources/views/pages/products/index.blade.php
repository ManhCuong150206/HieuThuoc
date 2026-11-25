@extends('layouts.app')
@section('title', $onSale ? 'Sản phẩm đang giảm giá' : ($category?->name ?? 'Danh mục sản phẩm'))
@section('content')
<div class="site-container page-stack">
    @if ($onSale)
        <x-section-title title="Sản phẩm đang giảm giá" :level="1" />
    @else
        <h1 class="sr-only">{{ $category?->name ?? 'Tất cả sản phẩm' }}</h1>
    @endif
    <div class="catalog-layout">
        <aside class="catalog-filters" aria-labelledby="filters-title">
            <h2 id="filters-title">Danh mục sản phẩm</h2>
            <div class="filter-group">
                <a href="{{ route('products.index') }}" @if (!$category && !$onSale) aria-current="page" @endif>Tất cả sản phẩm</a>
                @foreach ($categories as $item)
                    <a href="{{ route('categories.show', $item) }}" @if ($category?->id === $item->id && !$onSale) aria-current="page" @endif>{{ $item->name }}</a>
                @endforeach
            </div>
        </aside>
        <section class="catalog-results" aria-label="Danh sách sản phẩm">
            <div class="catalog-sort">
                <p>{{ $products->total() }} sản phẩm</p>
                <form class="catalog-sort-form" method="GET" action="{{ $category ? route('categories.show', $category) : route('products.index') }}">
                    @if ($onSale)<input type="hidden" name="on_sale" value="1">@endif
                    @if ($search)<input type="hidden" name="q" value="{{ $search }}">@endif
                    <label for="catalog-sort">Sắp xếp</label>
                    <select id="catalog-sort" name="sort">
                        <option value="name" @selected($sort === 'name')>Tên A–Z</option>
                        <option value="price_asc" @selected($sort === 'price_asc')>Giá tăng dần</option>
                        <option value="price_desc" @selected($sort === 'price_desc')>Giá giảm dần</option>
                    </select>
                    <button type="submit">Áp dụng</button>
                </form>
            </div>
            @if ($products->isEmpty())
                <div class="checkout-panel"><p>{{ $onSale ? 'Chưa có sản phẩm đang giảm giá phù hợp.' : 'Chưa có sản phẩm trong danh mục này.' }}</p></div>
            @else
                <div class="product-grid catalog-grid">
                    @foreach ($products as $product)<x-product-card :product="$product" />@endforeach
                </div>
            @endif
            <x-pagination :paginator="$products" />
        </section>
    </div>
</div>
@endsection
