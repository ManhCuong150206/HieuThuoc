@extends('layouts.admin')
@section('title', 'Quản lý đơn hàng')
@section('toast-feedback', 'true')

@section('admin-content')
<div class="admin-order-page">
    <header class="admin-order-title">
        <span class="admin-order-title-icon"><x-icon name="bag" :size="26" /></span>
        <h1 tabindex="-1" data-admin-order-heading>Quản lý đơn hàng</h1>
        <p class="admin-order-count"><strong data-admin-order-count>{{ number_format($orders->total(), 0, ',', '.') }}</strong> đơn hàng</p>
    </header>

    <form class="admin-order-filters" method="GET" action="{{ route('admin.orders.index') }}" aria-label="Lọc đơn hàng">
        <div class="ui-field">
            <label for="order-search">Mã đơn hàng</label>
            <div class="admin-order-search">
                <x-icon name="search" :size="18" />
                <input id="order-search" name="q" type="search" maxlength="100" value="{{ request('q') }}" placeholder="Nhập mã đơn hàng">
            </div>
        </div>
        <div class="ui-field">
            <label for="order-filter">Trạng thái</label>
            <select id="order-filter" name="status">
                <option value="">Tất cả trạng thái</option>
                @foreach ($statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-order-filter-actions">
            <x-button type="submit">Lọc đơn hàng</x-button>
            @if (request()->filled('q') || request()->filled('status'))
                <x-button :href="route('admin.orders.index')" variant="outline">Xóa bộ lọc</x-button>
            @endif
        </div>
    </form>

    <div class="admin-order-results" data-admin-order-results>
        @include('pages.admin.order-results')
    </div>
</div>
@endsection
