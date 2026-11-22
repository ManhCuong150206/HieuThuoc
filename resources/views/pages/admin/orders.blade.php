@extends('layouts.admin')
@section('title', 'Quản lý đơn hàng')
@section('admin-content')
<x-section-title title="Quản lý đơn hàng" :level="1" />
<form class="inline-form" method="GET">
    <label class="sr-only" for="order-search">Mã đơn</label><input id="order-search" name="q" value="{{ request('q') }}" placeholder="Mã đơn hàng">
    <label class="sr-only" for="order-filter">Trạng thái</label><select id="order-filter" name="status"><option value="">Tất cả trạng thái</option>@foreach ($statusLabels as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select>
    <x-button type="submit" variant="outline">Lọc đơn hàng</x-button>
</form>
<p>Tổng: {{ $orders->total() }} đơn</p>
@forelse ($orders as $order)
<section class="admin-panel order-card">
    <div class="order-card-heading"><h2>{{ $order->order_code }}</h2><x-badge>{{ $statusLabels[$order->status] ?? $order->status }}</x-badge></div>
    <p>{{ $order->created_at->format('d/m/Y') }} · {{ $order->recipient_name }} · {{ $order->recipient_phone }}</p>
    <p>{{ $order->shipping_address }}</p>
    <ul>@foreach ($order->items as $item)<li>{{ $item->product_name }} × {{ $item->quantity }} — {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ</li>@endforeach</ul>
    <p>Tổng: <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong> · Tiền mặt: {{ $order->payments->contains('status', 'SUCCESS') ? 'Thành công' : 'Chưa hoàn tất / đã hủy' }}</p>
    @php($next = ['PENDING' => ['CONFIRMED', 'CANCELLED'], 'CONFIRMED' => ['PROCESSING', 'SHIPPING', 'CANCELLED'], 'PROCESSING' => ['SHIPPING', 'CANCELLED'], 'SHIPPING' => ['DELIVERED'], 'DELIVERED' => ['COMPLETED']][$order->status] ?? [])
    @if ($next)
        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="inline-form">
            @csrf @method('PUT')
            <label for="order-status-{{ $order->id }}">Chuyển sang</label>
            <select id="order-status-{{ $order->id }}" name="status">@foreach ($next as $status)<option value="{{ $status }}">{{ $statusLabels[$status] }}</option>@endforeach</select>
            <x-button type="submit" variant="outline">Cập nhật</x-button>
        </form>
    @endif
</section>
@empty<section class="admin-panel"><p>Chưa có đơn hàng phù hợp.</p></section>@endforelse
<x-pagination :paginator="$orders" />
@endsection
