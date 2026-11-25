@extends('layouts.admin')
@section('title', 'Quản lý đánh giá')
@section('admin-content')
<x-section-title title="Quản lý đánh giá" :level="1" />
<nav class="admin-review-tabs" aria-label="Loại phản hồi">
    <a href="{{ route('admin.reviews.index') }}" @if (!$showComments) aria-current="page" @endif>Đánh giá sản phẩm</a>
    <a href="{{ route('admin.reviews.index', ['tab' => 'bai-viet']) }}" @if ($showComments) aria-current="page" @endif>Bình luận bài viết</a>
</nav>
@forelse ($entries as $entry)
    <section class="admin-panel admin-review">
        @if ($showComments)
            <h2>{{ $entry->post->title }}</h2>
            <p class="admin-review-meta">{{ $entry->authorName() }} · {{ $entry->created_at->format('d/m/Y') }}</p>
            <p class="discussion-text">{{ $entry->body }}</p>
            <div class="admin-review-actions">
                @if ($entry->post->isPublished())
                    <x-button variant="outline" :href="route('posts.show', ['post' => $entry->post, 'comment' => $entry->id]).'#binh-luan-'.$entry->id">Xem bài viết</x-button>
                @else
                    <span class="admin-review-meta">Bài viết chưa công khai</span>
                @endif
                <span class="admin-review-meta">{{ $entry->admin_reply !== null ? 'Đã trả lời' : 'Chưa trả lời' }}</span>
            </div>
        @else
            @php($product = $entry->orderItem?->product)
            <h2>{{ $entry->orderItem?->product_name }} — {{ $entry->rating }}/5</h2>
            <p class="admin-review-meta">{{ $entry->orderItem?->order?->user?->full_name ?? 'Khách hàng' }}</p>
            @if ($entry->comment !== null)<p class="discussion-text">{{ $entry->comment }}</p>@endif
            <div class="admin-review-actions">
                @if ($product?->is_active)
                    @if ($entry->isPubliclyVisible())
                        <x-button variant="outline" :href="route('products.show', ['product' => $product, 'review' => $entry->id]).'#danh-gia-'.$entry->id">Xem đánh giá</x-button>
                        <span class="admin-review-meta">{{ $entry->admin_reply !== null ? 'Đã trả lời' : 'Chưa trả lời' }}</span>
                    @else
                        <x-button variant="outline" :href="route('products.show', $product)">Xem sản phẩm</x-button>
                        <span class="admin-review-meta">Đánh giá chưa công khai</span>
                    @endif
                @else
                    <span class="admin-review-meta">Sản phẩm chưa công khai</span>
                @endif
                <form method="POST" action="{{ route('admin.reviews.destroy', $entry) }}" data-confirm="Bạn có muốn xóa đánh giá này?">
                    @csrf @method('DELETE')
                    <x-button type="submit" variant="outline">Xóa đánh giá</x-button>
                </form>
            </div>
        @endif
    </section>
@empty
    <section class="admin-panel"><p>{{ $showComments ? 'Chưa có bình luận bài viết.' : 'Chưa có đánh giá.' }}</p></section>
@endforelse
<x-pagination :paginator="$entries" />
@endsection
