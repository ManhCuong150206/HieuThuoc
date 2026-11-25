@extends('layouts.account', ['accountPageTitle' => 'Đơn hàng của tôi'])

@section('account-content')
    <section class="account-panel" id="don-hang">
        <h2>Tất cả đơn hàng ({{ $orders->total() }})</h2>
        @forelse ($orders as $order)
            <x-order-card :order="$order" :status-label="$statusLabels[strtoupper($order->status)] ?? $order->status" />
        @empty
            <p>Bạn chưa có đơn hàng nào.</p>
        @endforelse
        @if ($orders->hasPages())
            <nav class="profile-actions" aria-label="Các trang đơn hàng">
                @if ($orders->previousPageUrl())<x-button variant="outline" :href="$orders->previousPageUrl()">Trang trước</x-button>@endif
                <span>Trang {{ $orders->currentPage() }} / {{ $orders->lastPage() }}</span>
                @if ($orders->hasMorePages())<x-button variant="outline" :href="$orders->nextPageUrl()">Trang sau</x-button>@endif
            </nav>
        @endif
    </section>
@endsection
