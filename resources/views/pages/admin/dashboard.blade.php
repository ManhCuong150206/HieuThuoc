@extends('layouts.app')
@section('title', 'Quản trị nhà thuốc')
@section('body-class', 'admin')
@section('content')
<div class="admin-layout">
    <aside class="admin-sidebar">
        <p class="admin-brand">NHÀ THUỐC TRỰC TUYẾN</p>
        <p class="admin-space">KHÔNG GIAN QUẢN TRỊ</p>
        <nav aria-label="Điều hướng quản trị">
            <ul>
                @foreach ($menu as [$icon, $label])
                    <li>
                        @if ($loop->first)
                            <a href="{{ route('admin.dashboard') }}" aria-current="page"><x-icon :name="$icon" :size="20" />{{ $label }}</a>
                        @else
                            <span><x-icon :name="$icon" :size="20" />{{ $label }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </aside>
    <div class="admin-main">
        <header class="admin-heading">
            <x-section-title title="Tổng quan nhà thuốc" description="Theo dõi hoạt động và những việc cần xử lý hôm nay." :level="1" />
            <x-badge>Quản trị viên • Nhóm 10</x-badge>
        </header>
        <div class="admin-filters">
            <x-button variant="outline">Tháng 10, 2026 ⌄</x-button><x-button>Xuất báo cáo</x-button><span class="admin-demo">DỮ LIỆU MINH HỌA</span>
        </div>
        <section aria-label="Chỉ số tổng quan" class="admin-metrics">
            @foreach ($metrics as [$label, $value, $note])
                <div class="admin-panel metric"><h2>{{ $label }}</h2><p>{{ $value }}</p><small>{{ $note }}</small></div>
            @endforeach
        </section>
        <div class="admin-analysis">
            <section class="admin-panel admin-revenue">
                <h2>Doanh thu theo tuần</h2><p>Đơn vị: triệu đồng</p>
                <ul class="revenue-chart">
                    @foreach ($revenue as [$value, $week, $bar])
                        <li><strong>{{ $value }}</strong><span class="chart-bar chart-bar--{{ $bar }}" aria-hidden="true"></span><span>{{ $week }}</span></li>
                    @endforeach
                </ul>
            </section>
            <section class="admin-panel admin-alerts">
                <h2>Cần chú ý</h2>
                <ul>
                    @foreach ($alerts as [$title, $copy])
                        <li><h3>{{ $title }}</h3><p>{{ $copy }}</p></li>
                    @endforeach
                </ul>
                <x-button variant="outline" class="full-width">Kiểm tra tồn kho →</x-button>
            </section>
        </div>
        <section class="admin-panel admin-orders">
            <h2>Đơn hàng gần đây</h2>
            <div class="admin-table-scroll" tabindex="0" role="region" aria-label="Bảng đơn hàng gần đây">
                <table>
                    <thead><tr><th scope="col">Mã đơn</th><th scope="col">Khách hàng</th><th scope="col">Tổng tiền</th><th scope="col">Trạng thái</th><th scope="col">Thời gian</th></tr></thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>@foreach ($order as $value)<td>{{ $value }}</td>@endforeach</tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        <p class="admin-note">Số liệu minh họa • Quyền truy cập dành cho quản trị viên / nhân viên.</p>
    </div>
</div>
@endsection
