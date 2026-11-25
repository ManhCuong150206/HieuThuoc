@extends('layouts.admin')
@section('title', 'Quản trị nhà thuốc')
@section('admin-content')
<header class="admin-heading"><x-section-title title="Tổng quan nhà thuốc" description="Dữ liệu trực tiếp từ cửa hàng." :level="1" /><x-button :href="route('admin.orders.index')">Quản lý đơn hàng</x-button></header>
<section class="admin-metrics" aria-label="Chỉ số tổng quan">
    @foreach ([['Giá trị đơn chưa hủy', number_format($revenue, 0, ',', '.').'đ'], ['Đơn hàng', $orderCount], ['Sản phẩm', $productCount], ['Khách hàng', $customerCount]] as [$label, $value])
        <div class="admin-panel metric"><h2>{{ $label }}</h2><p>{{ $value }}</p></div>
    @endforeach
</section>
<section class="admin-panel admin-sales" aria-labelledby="daily-sales-heading">
    <header class="admin-sales-heading">
        <div>
            <h2 id="daily-sales-heading">Sản phẩm bán theo ngày</h2>
            <p>Số lượng sản phẩm trong đơn đã giao hoặc hoàn thành, tính theo ngày đặt.</p>
        </div>
        <p class="admin-sales-range">
            {{ $dailySales->first()['date']->format('d/m/Y') }} – {{ $dailySales->last()['date']->format('d/m/Y') }}
        </p>
    </header>
    <ol class="admin-sales-chart" role="list">
        @foreach ($dailySales as ['date' => $date, 'units' => $units])
            @php($isPeak = $units > 0 && $units === $peakDailySales)
            <li @class(['admin-sales-day', 'admin-sales-day-peak' => $isPeak])>
                <time datetime="{{ $date->toDateString() }}">{{ $date->format('d/m') }}</time>
                <div class="admin-sales-column">
                    <div class="admin-sales-bar" style="--bar-height: {{ 100 * $units / max(1, $peakDailySales) }}%">
                        <span class="admin-sales-value">
                            {{ number_format($units, 0, ',', '.') }}<span class="sr-only"> sản phẩm{{ $isPeak ? ', cao nhất trong kỳ' : '' }}</span>
                        </span>
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
</section>
<section class="admin-panel">
    <header class="admin-heading admin-panel-heading">
        <h2>Đơn hàng gần đây</h2>
        <x-button variant="outline" :href="route('admin.report')">Xuất báo cáo CSV</x-button>
    </header>
    <div class="admin-table-scroll"><table class="data-table">
        <thead><tr><th>Mã đơn</th><th>Người nhận</th><th class="num">Tổng tiền</th><th>Trạng thái</th></tr></thead>
        <tbody>@forelse ($orders as $order)
            <tr><td><a href="{{ route('admin.orders.index', ['q' => $order->order_code]) }}">{{ $order->order_code }}</a></td><td>{{ $order->recipient_name }}</td><td class="num">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td><td>{{ $statusLabels[$order->status] ?? $order->status }}</td></tr>
        @empty<tr><td colspan="4">Chưa có đơn hàng.</td></tr>@endforelse</tbody>
    </table></div>
</section>
@endsection
