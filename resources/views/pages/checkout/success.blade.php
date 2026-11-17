@extends('layouts.app')
@section('title', 'Đặt hàng thành công')
@section('content')
<div class="site-container confirmation-area">
    <section class="confirmation-card">
        <div class="success-mark"><x-icon name="success-check" :size="36" /></div>
        <h1>Đặt hàng thành công!</h1>
        <p class="success-message">Cảm ơn bạn đã tin tưởng An Tâm.<br>Đơn hàng {{ $order['code'] }} đang chờ xác nhận.</p>
        <x-badge class="uppercase">{{ $order['status'] }}</x-badge>
        <dl class="confirmation-details">
            <div><dt>Mã đơn hàng</dt><dd>{{ $order['code'] }}</dd></div>
            <div><dt>Thanh toán</dt><dd>{{ $order['payment'] }}</dd></div>
            <div><dt>Tổng thanh toán</dt><dd>{{ $order['total'] }}</dd></div>
            <div><dt>Email xác nhận</dt><dd>{{ $order['email'] }}</dd></div>
        </dl>
        <p class="confirmation-note">Bạn sẽ nhận được email xác nhận kèm chi tiết đơn hàng.</p>
        <div class="confirmation-actions">
            <x-button :href="route('account.index').'#don-hang'">Xem đơn hàng</x-button>
            <x-button variant="outline" :href="route('products.index')">Tiếp tục mua sắm</x-button>
        </div>
    </section>
</div>
@endsection
