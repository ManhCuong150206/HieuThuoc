@props(['order', 'items', 'statusLabel'])
<article class="order-card">
    <div class="order-card-heading"><h3>{{ $order->order_code }} &nbsp;•&nbsp; {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y') }}</h3><x-badge>{{ $statusLabel }}</x-badge></div>
    <p>{{ $items->sum('quantity') }} sản phẩm &nbsp;•&nbsp; <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></p>
    <details>
        <summary>Xem chi tiết đơn hàng</summary>
        <ul class="account-order-items">
            @foreach ($items as $item)
                <li>
                    {{ $item->product_name }} × {{ $item->quantity }} — {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ
                    @if ($order->status === 'COMPLETED' && !$item->review_id)
                        <form method="POST" action="{{ route('reviews.store', $item->id) }}" class="review-form">
                            @csrf
                            <label for="rating-{{ $item->id }}">Chấm điểm</label>
                            <select id="rating-{{ $item->id }}" name="rating">@foreach ([5, 4, 3, 2, 1] as $rating)<option value="{{ $rating }}">{{ $rating }}/5</option>@endforeach</select>
                            <label class="sr-only" for="comment-{{ $item->id }}">Nhận xét</label>
                            <textarea id="comment-{{ $item->id }}" name="comment" maxlength="500" placeholder="Chia sẻ trải nghiệm của bạn"></textarea>
                            <x-button type="submit" variant="outline">Gửi đánh giá</x-button>
                        </form>
                    @elseif ($item->review_id)
                        <p class="checkout-note">{{ $item->review_status === 'VISIBLE' ? 'Đánh giá đã được hiển thị.' : 'Đã gửi đánh giá.' }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
        <p>Người nhận: {{ $order->recipient_name }} · {{ $order->recipient_phone }}</p>
        <p>Địa chỉ nhận hàng: {{ $order->shipping_address }}</p>
    </details>
    <div class="profile-actions">
        <a class="text-action" href="{{ route('checkout.success', $order->id) }}">Thông tin thanh toán</a>
        <form method="POST" action="{{ route('orders.reorder', $order->id) }}">@csrf<x-button type="submit" variant="outline">Đặt lại</x-button></form>
        @if (in_array($order->status, ['PENDING', 'CONFIRMED', 'PROCESSING']))
            <form method="POST" action="{{ route('orders.cancel', $order->id) }}">@csrf<x-button type="submit" variant="outline">Hủy đơn</x-button></form>
        @endif
    </div>
</article>
