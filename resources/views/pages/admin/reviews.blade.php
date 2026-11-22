@extends('layouts.admin')
@section('title', 'Duyệt đánh giá')
@section('admin-content')
<x-section-title title="Duyệt đánh giá" :level="1" />
@forelse ($reviews as $review)
<section class="admin-panel">
    <h2>{{ $review->orderItem?->product_name }} — {{ $review->rating }}/5</h2>
    <p>{{ $review->orderItem?->order?->user?->full_name }} · {{ $review->comment }}</p>
    <form method="POST" action="{{ route('admin.reviews.update', $review) }}" class="inline-form">
        @csrf @method('PUT')
        <label for="review-{{ $review->id }}">Trạng thái</label><select id="review-{{ $review->id }}" name="status">@foreach (['PENDING' => 'Chờ duyệt', 'VISIBLE' => 'Hiển thị', 'HIDDEN' => 'Ẩn'] as $key => $label)<option value="{{ $key }}" @selected($review->status === $key)>{{ $label }}</option>@endforeach</select>
        <x-button type="submit" variant="outline">Lưu</x-button>
    </form>
</section>
@empty<section class="admin-panel"><p>Chưa có đánh giá.</p></section>@endforelse
<x-pagination :paginator="$reviews" />
@endsection
