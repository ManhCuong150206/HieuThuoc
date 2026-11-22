@extends('layouts.admin')
@section('title', ($record->exists ? 'Sửa ' : 'Thêm ').$title)
@section('admin-content')
<header class="admin-heading"><x-section-title :title="($record->exists ? 'Sửa ' : 'Thêm ').$title" :level="1" /><x-button variant="outline" :href="route('admin.resources.index', $resource)">Quay lại</x-button></header>
@php
$fields = match ($resource) {
    'products' => ['name' => ['Tên sản phẩm', 'text', 200, true], 'price' => ['Giá bán (đ)', 'number', null, true], 'quantity' => ['Số lượng tồn kho', 'number', null, true], 'brand' => ['Thương hiệu', 'text', 100, false], 'composition' => ['Thành phần', 'textarea', 500, false], 'used_for' => ['Công dụng', 'textarea', 500, false], 'how_to_use' => ['Cách dùng', 'textarea', 500, false], 'contraindications' => ['Chống chỉ định', 'textarea', 500, false], 'side_effects' => ['Tác dụng phụ', 'textarea', 500, false], 'image_url' => ['URL ảnh (https://...)', 'url', 500, false], 'expiry_date' => ['Hạn sử dụng', 'date', null, false]],
    'categories' => ['name' => ['Tên danh mục', 'text', 100, true]],
    'promotions' => ['name' => ['Tên ưu đãi', 'text', 150, true], 'description' => ['Mô tả', 'textarea', 500, false], 'discount_percent' => ['Phần trăm giảm (0–100)', 'number', null, true], 'starts_at' => ['Ngày bắt đầu', 'date', null, true], 'ends_at' => ['Ngày kết thúc', 'date', null, true]],
    'posts' => ['title' => ['Tiêu đề', 'text', 200, true], 'content' => ['Nội dung', 'textarea', 500, true], 'cover_url' => ['URL ảnh bìa (https://...)', 'url', 500, false], 'published_at' => ['Ngày xuất bản (bắt buộc khi xuất bản)', 'date', null, false]],
};
@endphp
<form method="POST" action="{{ $record->exists ? route('admin.resources.update', ['resource' => $resource, 'record' => $record->id]) : route('admin.resources.store', $resource) }}" class="admin-panel admin-form">
    @csrf @if ($record->exists) @method('PUT') @endif
    @if ($resource === 'products')
        <div class="ui-field"><label for="category-id">Danh mục</label><select id="category-id" name="category_id" required><option value="">Chọn danh mục</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $record->category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
    @endif
    @foreach ($fields as $name => [$label, $type, $max, $required])
        @php($value = old($name, $type === 'date' ? $record->$name?->format('Y-m-d') : ($name === 'price' ? (int) round((float) $record->price) : ($record->$name ?? (in_array($name, ['quantity', 'discount_percent']) ? 0 : '')))))
        <div class="ui-field">
            <label for="field-{{ $name }}">{{ $label }}</label>
            @if ($type === 'textarea')
                <textarea id="field-{{ $name }}" name="{{ $name }}" maxlength="{{ $max }}" rows="4" @required($required)>{{ $value }}</textarea>
            @else
                <input id="field-{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" @if($max) maxlength="{{ $max }}" @endif @if($type === 'number') min="0" step="1" @endif @required($required)>
            @endif
        </div>
    @endforeach
    @if (in_array($resource, ['products', 'promotions']))
        <div class="ui-field"><label for="is-active">Trạng thái</label><select id="is-active" name="is_active"><option value="1" @selected((int) old('is_active', $record->is_active ?? 1) === 1)>Đang bật</option><option value="0" @selected((int) old('is_active', $record->is_active ?? 1) === 0)>Đã tắt</option></select></div>
    @endif
    @if ($resource === 'posts')
        <div class="ui-field"><label for="post-status">Trạng thái</label><select id="post-status" name="status"><option value="DRAFT" @selected(old('status', $record->status) === 'DRAFT')>Bản nháp</option><option value="PUBLISHED" @selected(old('status', $record->status) === 'PUBLISHED')>Xuất bản</option></select></div>
    @endif
    <div><x-button type="submit">Lưu thông tin</x-button></div>
</form>
@endsection
