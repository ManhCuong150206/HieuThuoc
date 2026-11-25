@props(['resource', 'title', 'record', 'categories', 'promotionProducts'])

@php($heading = match ($resource) {
    'products' => 'Thêm sản phẩm',
    'categories' => 'Thêm danh mục',
    'promotions' => 'Thêm khuyến mãi',
    'posts' => 'Thêm bài viết',
})

<dialog id="resource-create-dialog" class="admin-resource-dialog" aria-labelledby="resource-create-title"
    data-edit-title="Sửa {{ mb_strtolower($title, 'UTF-8') }}"
    @if ($errors->productCreate->any() || request()->boolean('them')) data-open-on-load @endif>
    <form method="POST" action="{{ route('admin.resources.store', ['resource' => $resource, ...request()->only(['q', 'page'])]) }}" enctype="multipart/form-data" data-admin-create-form>
        @csrf
        <input type="hidden" name="_method" value="PUT" disabled>
        <header class="admin-resource-dialog-header">
            <h2 id="resource-create-title" data-admin-form-title>{{ $heading }}</h2>
            <button type="button" class="admin-resource-dialog-close" aria-label="Đóng hộp thoại" data-admin-create-close>&times;</button>
        </header>
        <div class="admin-resource-dialog-fields" data-admin-create-fields>
            <ul class="admin-create-errors admin-field-error" role="alert" tabindex="-1" data-admin-create-errors hidden></ul>
            <p class="admin-resource-note" role="status" data-admin-form-loading hidden>Đang tải thông tin…</p>
            <div class="admin-resource-form-fields" data-admin-form-content>
                @if ($errors->productCreate->any())
                    <p class="admin-field-error" role="alert">Vui lòng kiểm tra các trường được báo lỗi.</p>
                @endif
                <x-admin.resource-fields :resource="$resource" :record="$record" :categories="$categories" :promotion-products="$promotionProducts" :assigned-product-ids="[]" />
            </div>
        </div>
        <footer class="admin-resource-dialog-actions">
            <x-button variant="outline" data-admin-create-close>Hủy</x-button>
            <x-button type="submit">Lưu thông tin</x-button>
        </footer>
    </form>
</dialog>
