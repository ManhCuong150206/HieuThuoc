@php
    $scope = (int) old('applies_to_all_products', $record->exists && $record->applies_to_all_products);
    $oldIds = old('product_ids');
    $selectedIds = is_string($oldIds) ? json_decode($oldIds) : $assignedProductIds;
    $selectedIds = is_array($selectedIds) ? array_filter($selectedIds, 'is_int') : [];
    $selectedLookup = array_fill_keys($selectedIds, true);
@endphp
<fieldset class="admin-promotion-products" data-promotion-products>
    <legend>Phạm vi khuyến mãi</legend>
    <div class="ui-field">
        <label for="promotion-scope" class="sr-only">Sản phẩm được áp dụng</label>
        <select id="promotion-scope" name="applies_to_all_products">
            <option value="0" @selected($scope === 0)>Chỉ sản phẩm đã chọn</option>
            <option value="1" @selected($scope === 1)>Tất cả sản phẩm</option>
        </select>
    </div>
    <div class="admin-promotion-picker" data-promotion-picker @if ($scope === 1) hidden @endif>
        <input type="hidden" name="product_ids" value="{{ json_encode(array_values($selectedIds)) }}">
        <div class="ui-field">
            <label for="promotion-product-search">Tìm sản phẩm</label>
            <input id="promotion-product-search" type="search" placeholder="Nhập tên sản phẩm" autocomplete="off" data-promotion-search>
        </div>
        <div class="admin-promotion-controls">
            <label class="admin-promotion-choice">
                <input type="checkbox" data-promotion-all>
                <span>Chọn tất cả kết quả</span>
            </label>
            <label class="admin-promotion-choice">
                <input type="checkbox" data-promotion-selected>
                <span>Chỉ xem đã chọn</span>
            </label>
            <p class="admin-resource-note" role="status" data-promotion-count></p>
        </div>
        <div class="admin-promotion-list" role="group" aria-label="Danh sách sản phẩm" tabindex="0">
            @foreach ($promotionProducts as $product)
                <label class="admin-promotion-choice" data-promotion-option>
                    <input type="checkbox" value="{{ $product->id }}" @checked(isset($selectedLookup[$product->id]))>
                    <span><span data-promotion-name>{{ $product->name }}</span>@unless ($product->is_active) <small class="admin-resource-note">Đã ẩn</small>@endunless</span>
                </label>
            @endforeach
            <p class="admin-resource-note admin-promotion-empty" data-promotion-empty hidden>Không có sản phẩm phù hợp.</p>
        </div>
    </div>
</fieldset>
