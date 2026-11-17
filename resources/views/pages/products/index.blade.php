@extends('layouts.app')
@section('title', $category['name'] ?? 'Danh mục sản phẩm')
@section('content')
<div class="site-container page-stack">
    <x-breadcrumb :items="[['label' => $category['name'] ?? 'Danh mục sản phẩm']]" />
    <x-section-title eyebrow="TÌM ĐIỀU BẠN CẦN" :title="$category['name'] ?? 'Chăm sóc sức khỏe mỗi ngày'"
        description="Dễ dàng tìm kiếm theo tên sản phẩm, hoạt chất hoặc nhu cầu." :level="1" />
    <div class="catalog-layout">
        <aside class="catalog-filters" aria-labelledby="filters-title">
            <h2 id="filters-title">Bộ lọc tìm kiếm</h2>
            <div class="filter-group">
                <h3>Danh mục</h3>
                <a href="{{ route('products.index') }}" @if (!$category) aria-current="page" @endif>
                    <span aria-hidden="true">{{ $category ? '□' : '●' }}</span> Tất cả sản phẩm
                </a>
                @foreach ($categories as $item)
                    <a href="{{ route('categories.show', $item['slug']) }}" @if (($category['slug'] ?? null) === $item['slug']) aria-current="page" @endif>
                        <span aria-hidden="true">{{ ($category['slug'] ?? null) === $item['slug'] ? '●' : '□' }}</span> {{ $item['name'] }}
                    </a>
                @endforeach
            </div>
            @foreach ($filters as $label => $options)
                <fieldset class="filter-group">
                    <legend>{{ $label }}</legend>
                    @foreach ($options as $option)
                        <label class="check-label"><input type="checkbox"> {{ $option }}</label>
                    @endforeach
                </fieldset>
            @endforeach
            <x-button variant="outline" class="full-width" :href="route('products.index')">Đặt lại bộ lọc</x-button>
        </aside>
        <section class="catalog-results" aria-label="Danh sách sản phẩm">
            <div class="catalog-sort"><p>{{ $count }} sản phẩm minh họa</p><x-button variant="outline">Sắp xếp: Phổ biến ⌄</x-button></div>
            <div class="product-grid catalog-grid">
                @foreach ($products as $product)<x-product-card :product="$product" />@endforeach
            </div>
            <x-pagination />
            <p class="catalog-note">Giá và thông tin sản phẩm trong bản thiết kế chỉ dùng để minh họa.</p>
        </section>
    </div>
</div>
@endsection
