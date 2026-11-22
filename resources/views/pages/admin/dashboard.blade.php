@extends('layouts.admin')
@section('title', 'Quản trị nhà thuốc')
@section('admin-content')
<header class="admin-heading"><x-section-title title="Tổng quan nhà thuốc" description="Dữ liệu trực tiếp từ cửa hàng." :level="1" /><x-button :href="route('admin.orders.index')">Quản lý đơn hàng</x-button></header>
<section class="admin-metrics" aria-label="Chỉ số tổng quan">
    @foreach ([['Giá trị đơn chưa hủy', number_format($revenue, 0, ',', '.').'đ'], ['Đơn hàng', $orderCount], ['Sản phẩm', $productCount], ['Khách hàng', $customerCount]] as [$label, $value])
        <div class="admin-panel metric"><h2>{{ $label }}</h2><p>{{ $value }}</p></div>
    @endforeach
</section>
<section class="admin-panel">
    <h2>Đơn hàng gần đây</h2>
    <div class="admin-table-scroll"><table class="data-table">
        <thead><tr><th>Mã đơn</th><th>Người nhận</th><th class="num">Tổng tiền</th><th>Trạng thái</th></tr></thead>
        <tbody>@forelse ($orders as $order)
            <tr><td><a href="{{ route('admin.orders.index', ['q' => $order->order_code]) }}">{{ $order->order_code }}</a></td><td>{{ $order->recipient_name }}</td><td class="num">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td><td>{{ $statusLabels[$order->status] ?? $order->status }}</td></tr>
        @empty<tr><td colspan="4">Chưa có đơn hàng.</td></tr>@endforelse</tbody>
    </table></div>
</section>
<div class="admin-analysis">
    <section class="admin-panel">
        <h2>Tồn kho từ 5 sản phẩm trở xuống</h2>
        <ul class="admin-list">@forelse ($lowStock as $product)<li><a href="{{ route('admin.resources.edit', ['resource' => 'products', 'record' => $product->id]) }}">{{ $product->name }}</a><strong>{{ $product->quantity }}</strong></li>@empty<li>Không có sản phẩm sắp hết.</li>@endforelse</ul>
        <a class="text-action" href="{{ route('admin.resources.index', 'products') }}">Cập nhật tồn kho</a>
    </section>
    <section class="admin-panel">
        <h2>Hết hạn / hết hạn trong 30 ngày</h2>
        <ul class="admin-list">@forelse ($expiring as $product)<li><a href="{{ route('admin.resources.edit', ['resource' => 'products', 'record' => $product->id]) }}">{{ $product->name }}</a><span>{{ $product->expiry_date->format('d/m/Y') }}</span></li>@empty<li>Không có cảnh báo hạn dùng.</li>@endforelse</ul>
    </section>
</div>
@endsection
