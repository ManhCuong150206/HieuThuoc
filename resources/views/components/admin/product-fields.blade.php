@props(['categories', 'record' => null])

@php
    $fieldErrors = $errors->getBag($record ? 'default' : 'productCreate');
    $valueFor = static function (string $name, $default = '') use ($record, $fieldErrors) {
        $value = $record || $fieldErrors->any() ? old($name, $default) : $default;

        return is_scalar($value) ? $value : $default;
    };
    $fields = [
        'name' => ['Tên sản phẩm', 'text', 200, true],
        'price' => ['Giá bán (đ)', 'number', null, true],
        'quantity' => ['Số lượng tồn kho', 'number', null, true],
        'brand' => ['Thương hiệu', 'text', 100, false],
        'composition' => ['Thành phần', 'textarea', 500, false],
        'used_for' => ['Công dụng', 'textarea', 500, false],
        'how_to_use' => ['Cách dùng', 'textarea', 500, false],
        'contraindications' => ['Chống chỉ định', 'textarea', 500, false],
        'side_effects' => ['Tác dụng phụ', 'textarea', 500, false],
        'image' => ['Ảnh sản phẩm', 'file', null, false],
        'expiry_date' => ['Hạn sử dụng', 'date', null, false],
    ];
@endphp

<div class="ui-field">
    <label for="product-category">Danh mục</label>
    <select id="product-category" name="category_id" required @if (!$record) autofocus @endif
        @if ($fieldErrors->has('category_id')) aria-invalid="true" aria-describedby="product-category-error" @endif>
        <option value="">Chọn danh mục</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) $valueFor('category_id', $record?->category_id) === (string) $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    @if ($fieldErrors->has('category_id'))
        <p class="admin-field-error" id="product-category-error">{{ $fieldErrors->first('category_id') }}</p>
    @endif
</div>

@foreach ($fields as $name => [$label, $type, $max, $required])
    @php
        $default = match ($name) {
            'price' => (int) round((float) $record?->price),
            'quantity' => $record?->quantity ?? 0,
            'expiry_date' => $record?->expiry_date?->format('Y-m-d'),
            default => $record?->$name ?? '',
        };
        $invalid = $fieldErrors->has($name);
    @endphp

    <div class="ui-field">
        <label for="product-field-{{ $name }}">{{ $label }}</label>
        @if ($type === 'file')
            @if ($record)
                <x-product-image :product="$record" loading="eager" class="admin-product-image-preview" />
            @endif
            <input id="product-field-{{ $name }}" name="{{ $name }}" type="file" class="admin-product-image-input" accept="image/jpeg,image/png,image/webp"
                aria-describedby="product-image-hint{{ $invalid ? ' product-error-'.$name : '' }}" @if ($invalid) aria-invalid="true" @endif>
            <p class="admin-resource-note" id="product-image-hint">JPG, PNG hoặc WebP, tối đa 5 MB. {{ $fieldErrors->any() ? 'Nếu đã chọn ảnh, vui lòng chọn lại.' : '' }}</p>
        @elseif ($type === 'textarea')
            <textarea id="product-field-{{ $name }}" name="{{ $name }}" maxlength="{{ $max }}" rows="4" @required($required)
                @if ($invalid) aria-invalid="true" aria-describedby="product-error-{{ $name }}" @endif>{{ $valueFor($name, $default) }}</textarea>
        @else
            <input id="product-field-{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $valueFor($name, $default) }}"
                @if ($max) maxlength="{{ $max }}" @endif @if ($type === 'number') min="0" step="1" @endif @required($required)
                @if ($invalid) aria-invalid="true" aria-describedby="product-error-{{ $name }}" @endif>
        @endif
        @if ($invalid)
            <p class="admin-field-error" id="product-error-{{ $name }}">{{ $fieldErrors->first($name) }}</p>
        @endif
    </div>
@endforeach

<div class="ui-field">
    <label for="product-is-active">Trạng thái</label>
    <select id="product-is-active" name="is_active"
        @if ($fieldErrors->has('is_active')) aria-invalid="true" aria-describedby="product-status-error" @endif>
        <option value="1" @selected((int) $valueFor('is_active', $record?->is_active ?? 1) === 1)>Đang bật</option>
        <option value="0" @selected((int) $valueFor('is_active', $record?->is_active ?? 1) === 0)>Đã tắt</option>
    </select>
    @if ($fieldErrors->has('is_active'))
        <p class="admin-field-error" id="product-status-error">{{ $fieldErrors->first('is_active') }}</p>
    @endif
</div>
