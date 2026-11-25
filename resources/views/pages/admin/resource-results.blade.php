<section class="admin-panel" data-admin-resource-results
    @if ($resource === 'products')
        data-admin-product-selection
        data-account-id="{{ auth()->id() }}"
        data-search="{{ $search }}"
        data-total="{{ $records->total() }}"
        @if (session('admin_products_bulk_saved')) data-bulk-saved @endif
        @if ($deletedProduct = session('admin_product_deleted')) data-deleted-product="{{ $deletedProduct }}" @endif
    @endif
>
    <div class="admin-resource-toolbar">
        <p>Tổng: {{ number_format($records->total(), 0, ',', '.') }}</p>
        @if ($resource === 'products')
            <div class="admin-bulk-controls" data-bulk-controls hidden>
                <p role="status" data-bulk-count>Đã chọn 0 sản phẩm</p>
                <button type="button" class="text-action" data-bulk-all hidden>Chọn tất cả {{ number_format($records->total(), 0, ',', '.') }} sản phẩm{{ $search !== null ? ' tìm được' : '' }}</button>
                <button type="button" class="text-action" data-bulk-clear hidden>Bỏ chọn</button>
                <x-button variant="outline" data-bulk-open disabled>Sửa hàng loạt</x-button>
            </div>
        @endif
    </div>
    @if ($resource === 'products')
        @include('pages.admin.product-bulk-edit')
    @endif
    <div class="admin-table-scroll"><table class="data-table">
        <thead><tr>
            @if ($resource === 'products')
                <th scope="col" class="admin-product-check-cell">
                    <label class="admin-product-check">
                        <input type="checkbox" aria-label="Chọn tất cả sản phẩm trên trang này" data-bulk-page disabled>
                    </label>
                </th>
            @endif
            <th scope="col">ID</th><th scope="col">Tên</th><th scope="col">Thông tin</th><th scope="col">Thao tác</th>
        </tr></thead>
        <tbody>@forelse ($records as $record)
            <tr>
                @if ($resource === 'products')
                    <td class="admin-product-check-cell">
                        <label class="admin-product-check">
                            <input type="checkbox" value="{{ $record->id }}" aria-label="Chọn {{ $record->name }}" data-bulk-product disabled>
                        </label>
                    </td>
                @endif
                <td>{{ $record->id }}</td><td>{{ $resource === 'posts' ? $record->title : $record->name }}</td>
                <td>
                    @if ($resource === 'products'){{ number_format($record->price, 0, ',', '.') }}đ · Kho: {{ $record->quantity }} · {{ $record->is_active ? 'Đang bán' : 'Đã ẩn' }}
                    @elseif ($resource === 'promotions')
                        Giảm {{ $record->discount_percent }}% · {{ $record->is_active ? 'Đang bật' : 'Đã tắt' }} · {{ $record->starts_at->format('d/m/Y') }}–{{ $record->ends_at->format('d/m/Y') }}
                        <div class="admin-resource-note">{{ $record->applies_to_all_products ? 'Tất cả sản phẩm' : number_format($record->products_count, 0, ',', '.').' sản phẩm được áp dụng' }}</div>
                    @elseif ($resource === 'posts'){{ $record->status === 'PUBLISHED' ? 'Đã xuất bản' : 'Bản nháp' }}
                    @else—@endif
                </td>
                <td><div @class(['inline-form', 'admin-product-actions' => $resource === 'products'])>
                    <a class="text-action" aria-haspopup="dialog" aria-controls="resource-create-dialog" data-admin-edit-open="{{ $record->id }}"
                        href="{{ route('admin.resources.edit', ['resource' => $resource, 'record' => $record->id, 'q' => $search, 'page' => $records->currentPage() > 1 ? $records->currentPage() : null]) }}">Sửa</a>
                    <form method="POST" action="{{ route('admin.resources.destroy', ['resource' => $resource, 'record' => $record->id]) }}" data-confirm="{{ $resource === 'categories' ? 'Xóa danh mục này?' : 'Ngừng sử dụng / ẩn mục này?' }}">
                        @csrf @method('DELETE')<button type="submit" class="text-action">{{ $resource === 'categories' ? 'Xóa' : 'Ngừng / ẩn' }}</button>
                    </form>
                    @if ($resource === 'products')
                        <form method="POST" action="{{ route('admin.products.destroy', $record->id) }}"
                            data-confirm="Xóa vĩnh viễn «{{ $record->name }}»? Sản phẩm sẽ được gỡ khỏi các giỏ hàng.">
                            @csrf
                            @method('DELETE')
                            @if ($search !== null)
                                <input type="hidden" name="q" value="{{ $search }}">
                            @endif
                            <button type="submit" class="text-action admin-product-delete" aria-label="Xóa {{ $record->name }}">Xóa</button>
                        </form>
                    @endif
                </div></td>
            </tr>
        @empty<tr><td colspan="{{ $resource === 'products' ? 5 : 4 }}">Chưa có dữ liệu phù hợp.</td></tr>@endforelse</tbody>
    </table></div>
    <x-pagination :paginator="$records" />
</section>
