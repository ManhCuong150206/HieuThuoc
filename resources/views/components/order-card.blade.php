@props(['order'])
<article class="order-card">
    <div class="order-card-heading"><h3>{{ $order[0] }} &nbsp;•&nbsp; Tháng 10, 2026</h3><x-badge>{{ $order[1] }}</x-badge></div>
    <div class="order-card-content">
        <div class="order-card-art"><x-icon name="account-product" /></div>
        <div class="order-card-copy"><h4>{{ $order[2] }}</h4><p>2 sản phẩm &nbsp;•&nbsp; {{ $order[3] }}</p></div>
        <x-button variant="outline">{{ $order[4] }}</x-button>
    </div>
</article>
