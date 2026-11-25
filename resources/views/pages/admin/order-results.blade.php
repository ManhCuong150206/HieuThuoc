    @forelse ($orders as $order)
        @php($next = $statusTransitions[$order->status] ?? [])
        <article class="admin-order-card" data-admin-order="{{ $order->id }}" aria-labelledby="order-code-{{ $order->id }}">
            <header class="admin-order-card-header">
                <div>
                    <h2 id="order-code-{{ $order->id }}" tabindex="-1">{{ $order->order_code }}</h2>
                    <time datetime="{{ $order->created_at->toDateString() }}">{{ $order->created_at->format('d/m/Y') }}</time>
                </div>
                <x-badge class="admin-order-state" :data-order-status="$order->status">{{ $statusLabels[$order->status] ?? $order->status }}</x-badge>
            </header>

            <div class="admin-order-details">
                <div class="admin-order-recipient">
                    <h3>Người nhận</h3>
                    <p class="admin-order-recipient-name">{{ $order->recipient_name }}</p>
                    <p>{{ $order->recipient_phone }}</p>
                    <p>{{ $order->shipping_address }}</p>
                </div>
                <div>
                    <h3>Sản phẩm</h3>
                    <ul class="admin-order-items">
                        @foreach ($order->items as $item)
                            <li>
                                <span>{{ $item->product_name }}</span>
                                <span class="admin-order-quantity">× {{ $item->quantity }}</span>
                                <strong>{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <footer class="admin-order-card-footer">
                <dl class="admin-order-summary">
                    <div>
                        <dt>Tổng tiền</dt>
                        <dd class="admin-order-total">{{ number_format($order->total_amount, 0, ',', '.') }}đ</dd>
                    </div>
                    <div>
                        <dt>Tiền mặt</dt>
                        <dd>{{ $order->payments->contains('status', 'SUCCESS') ? 'Thành công' : 'Chưa hoàn tất / đã hủy' }}</dd>
                    </div>
                </dl>
                @if ($next)
                    <form method="POST" action="{{ route('admin.orders.update', ['order' => $order, ...$orderListQuery]) }}" class="admin-order-update" data-admin-order-update aria-labelledby="order-code-{{ $order->id }}">
                        @csrf
                        @method('PUT')
                        <div class="ui-field">
                            <label for="order-status-{{ $order->id }}">Chuyển trạng thái</label>
                            <select id="order-status-{{ $order->id }}" name="status">
                                @foreach ($next as $status)
                                    <option value="{{ $status }}">{{ $status === 'CANCELLED' ? 'Hủy đơn' : $statusLabels[$status] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-button type="submit" variant="outline">Cập nhật</x-button>
                    </form>
                @endif
            </footer>
        </article>
    @empty
        <p class="admin-order-empty">Chưa có đơn hàng phù hợp.</p>
    @endforelse

    <x-pagination :paginator="$orders" />
