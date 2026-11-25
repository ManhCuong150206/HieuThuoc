@props(['order', 'statusLabel'])
<article class="order-card">
    <header class="order-card-heading">
        <div>
            <h3>{{ $order->order_code }}</h3>
            <p class="checkout-note">{{ $order->created_at->format('d/m/Y') }} · {{ $order->items->sum('quantity') }} sản phẩm</p>
        </div>
        <div class="order-card-amount"><x-badge>{{ $statusLabel }}</x-badge><strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></div>
    </header>
    <details>
        <summary>Xem chi tiết đơn hàng</summary>
        <ul class="account-order-items">
            @foreach ($order->items as $item)
                <li>
                    <x-order-product-line :item="$item"
                        :href="$item->product?->is_active ? route('products.show', $item->product_id) : null" />
                    @if ($order->status === 'COMPLETED' && !$item->review)
                        <form method="POST" action="{{ route('reviews.store', $item->id) }}" class="review-form" data-review-form>
                            @csrf
                            <fieldset class="review-rating">
                                <legend>Chấm điểm</legend>
                                <div class="review-stars">
                                    @foreach (range(1, 5) as $rating)
                                        <label class="review-star">
                                            <input class="sr-only" type="radio" name="rating" value="{{ $rating }}" required>
                                            <span aria-hidden="true"></span>
                                            <span class="sr-only">{{ $rating }} sao</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <label class="sr-only" for="comment-{{ $item->id }}">Nhận xét</label>
                            <textarea id="comment-{{ $item->id }}" name="comment" maxlength="500" placeholder="Chia sẻ trải nghiệm của bạn"></textarea>
                            <p class="auth-field-error" role="alert" data-review-error hidden></p>
                            <x-button type="submit" variant="outline">Gửi đánh giá</x-button>
                        </form>
                        <p class="checkout-note" tabindex="-1" data-review-status hidden></p>
                    @elseif ($item->review)
                        <p class="checkout-note">{{ $item->review->status === 'HIDDEN' ? 'Đánh giá đang bị ẩn.' : 'Đánh giá đã được hiển thị.' }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="order-card-details">
            <div>
                <h4>Giao hàng</h4>
                <p>{{ $order->recipient_name }} · {{ $order->recipient_phone }}</p>
                <p>{{ $order->shipping_address }}</p>
            </div>
            <div>
                <h4>Chi phí đơn hàng</h4>
                <dl>
                    <div><dt>Tạm tính</dt><dd>{{ number_format($order->subtotal, 0, ',', '.') }}đ</dd></div>
                    <div><dt>Phí giao hàng</dt><dd>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</dd></div>
                    @if ($order->discount_amount > 0)
                        <div><dt>Giảm giá</dt><dd>−{{ number_format($order->discount_amount, 0, ',', '.') }}đ</dd></div>
                    @endif
                </dl>
            </div>
        </div>
    </details>
    <div class="profile-actions">
        <a class="text-action" href="{{ route('checkout.success', $order->id) }}">Thông tin thanh toán</a>
        <form method="POST" action="{{ route('orders.reorder', $order->id) }}">@csrf<x-button type="submit" variant="outline">Đặt lại</x-button></form>
        @if (in_array($order->status, ['PENDING', 'CONFIRMED', 'PROCESSING']))
            <form method="POST" action="{{ route('orders.cancel', $order->id) }}">@csrf<x-button type="submit" variant="outline">Hủy đơn</x-button></form>
        @elseif ($order->status === 'SHIPPING')
            <form method="POST" action="{{ route('orders.confirm-delivery', $order->id) }}">@csrf<x-button type="submit" variant="outline">Xác nhận đã nhận hàng</x-button></form>
        @endif
    </div>
</article>
