@props(['order', 'items', 'statusLabel'])
<article class="order-card">
    <div class="order-card-heading"><h3>{{ $order->order_code }} &nbsp;•&nbsp; {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y') }}</h3><x-badge>{{ $statusLabel }}</x-badge></div>
    <p>{{ $items->sum('quantity') }} sản phẩm &nbsp;•&nbsp; <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></p>
    <details>
        <summary>Xem chi tiết đơn hàng</summary>
        <ul class="account-order-items">
            @foreach ($items as $item)
                <li>{{ $item->product_name }} × {{ $item->quantity }} — {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ</li>
            @endforeach
        </ul>
        <p>Người nhận: {{ $order->recipient_name }} · {{ $order->recipient_phone }}</p>
        <p>Địa chỉ nhận hàng: {{ $order->shipping_address }}</p>
    </details>
</article>
