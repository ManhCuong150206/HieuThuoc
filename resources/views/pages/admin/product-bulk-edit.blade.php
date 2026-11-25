<dialog class="admin-bulk-dialog" aria-labelledby="product-bulk-title" data-bulk-dialog>
    @php($hasBulkErrors = old('scope') && $errors->any())
    <form method="POST" action="{{ route('admin.products.bulk-update') }}" class="admin-bulk-form" data-bulk-form>
        @csrf
        @method('PATCH')
        <input type="hidden" name="q" value="{{ $search }}">
        <input type="hidden" name="page" value="{{ $records->currentPage() }}">
        <input type="hidden" name="scope">
        <input type="hidden" name="ids">
        <input type="hidden" name="selected_count">
        <header>
            <h2 id="product-bulk-title">Sửa hàng loạt</h2>
            <p class="admin-resource-note"><span data-bulk-dialog-count></span>. Để trống hoặc chọn “Giữ nguyên” cho mục bạn muốn giữ.</p>
        </header>
        @if ($hasBulkErrors)
            <ul class="feedback-message feedback-error admin-bulk-errors" role="alert" data-bulk-errors>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <div class="field-pair">
            <div class="ui-field">
                <label for="bulk-quantity">Tồn kho mới mỗi sản phẩm</label>
                <input id="bulk-quantity" name="quantity" type="number" min="0" max="1000000" step="1" value="{{ $hasBulkErrors && is_scalar(old('quantity')) ? old('quantity') : '' }}">
            </div>
            <div class="ui-field">
                <label for="bulk-price">Giá bán mới (đ)</label>
                <input id="bulk-price" name="price" type="number" min="0" max="1000000000" step="1" value="{{ $hasBulkErrors && is_scalar(old('price')) ? old('price') : '' }}">
            </div>
            <div class="ui-field">
                <label for="bulk-category">Danh mục</label>
                <select id="bulk-category" name="category_id">
                    <option value="">Giữ nguyên</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($hasBulkErrors && (int) old('category_id') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ui-field">
                <label for="bulk-status">Trạng thái</label>
                <select id="bulk-status" name="is_active">
                    <option value="">Giữ nguyên</option>
                    <option value="1" @selected($hasBulkErrors && old('is_active') === '1')>Hiện / Đang bán</option>
                    <option value="0" @selected($hasBulkErrors && old('is_active') === '0')>Ẩn / Ngừng bán</option>
                </select>
            </div>
        </div>
        <div class="admin-bulk-actions">
            <x-button variant="outline" data-bulk-close>Hủy</x-button>
            <x-button type="submit" data-bulk-submit disabled>Áp dụng thay đổi</x-button>
        </div>
    </form>
</dialog>
