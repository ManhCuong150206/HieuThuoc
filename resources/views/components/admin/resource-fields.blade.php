@props(['resource', 'record', 'categories', 'promotionProducts', 'assignedProductIds'])

@php
$fields = match ($resource) {
    'products' => [],
    'categories' => ['name' => ['Tên danh mục', 'text', 100, true]],
    'promotions' => ['name' => ['Tên ưu đãi', 'text', 150, true], 'description' => ['Mô tả', 'textarea', 500, false], 'discount_percent' => ['Phần trăm giảm (0–100)', 'number', null, true], 'starts_at' => ['Ngày bắt đầu', 'date', null, true], 'ends_at' => ['Ngày kết thúc', 'date', null, true]],
    'posts' => ['title' => ['Tiêu đề', 'text', 200, true], 'content' => ['Nội dung', 'textarea', 500, true], 'cover_url' => ['Đường dẫn ảnh bìa', 'text', 500, false], 'published_at' => ['Ngày xuất bản (bắt buộc khi xuất bản)', 'date', null, false]],
};
@endphp
    @if ($resource === 'products')
        <x-admin.product-fields :record="$record->exists ? $record : null" :categories="$categories" />
    @endif
    @foreach ($fields as $name => [$label, $type, $max, $required])
        @php($value = old($name, $type === 'date' ? $record->$name?->format('Y-m-d') : ($record->$name ?? ($name === 'discount_percent' ? 0 : ''))))
        <div class="ui-field">
            <label for="field-{{ $name }}">{{ $label }}</label>
            @if ($type === 'textarea')
                <textarea id="field-{{ $name }}" name="{{ $name }}" maxlength="{{ $max }}" rows="4" @required($required)>{{ $value }}</textarea>
            @else
                <input id="field-{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" @if($max) maxlength="{{ $max }}" @endif @if($type === 'number') min="0" step="1" @endif @required($required) @if($loop->first && ! $record->exists) autofocus @endif>
            @endif
        </div>
    @endforeach
    @if ($resource === 'promotions')
        <div class="ui-field"><label for="is-active">Trạng thái</label><select id="is-active" name="is_active"><option value="1" @selected((int) old('is_active', $record->is_active ?? 1) === 1)>Đang bật</option><option value="0" @selected((int) old('is_active', $record->is_active ?? 1) === 0)>Đã tắt</option></select></div>
        @include('pages.admin.promotion-products')
    @endif
    @if ($resource === 'posts')
        <div class="ui-field"><label for="post-status">Trạng thái</label><select id="post-status" name="status"><option value="DRAFT" @selected(old('status', $record->status) === 'DRAFT')>Bản nháp</option><option value="PUBLISHED" @selected(old('status', $record->status) === 'PUBLISHED')>Xuất bản</option></select></div>
    @endif
