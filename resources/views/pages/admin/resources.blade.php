@extends('layouts.admin')
@section('title', $title)
@section('admin-content')
<header class="admin-heading"><x-section-title :title="$title" :level="1" /><x-button :href="route('admin.resources.create', $resource)">Thêm mới</x-button></header>
<form class="inline-form" method="GET"><label class="sr-only" for="admin-search">Tìm kiếm</label><input id="admin-search" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên" maxlength="100"><x-button type="submit" variant="outline">Tìm kiếm</x-button></form>
<section class="admin-panel">
    <p>Tổng: {{ $records->total() }}</p>
    <div class="admin-table-scroll"><table class="data-table">
        <thead><tr><th>ID</th><th>Tên</th><th>Thông tin</th><th>Thao tác</th></tr></thead>
        <tbody>@forelse ($records as $record)
            <tr><td>{{ $record->id }}</td><td>{{ $resource === 'posts' ? $record->title : $record->name }}</td>
                <td>
                    @if ($resource === 'products'){{ number_format($record->price, 0, ',', '.') }}đ · Kho: {{ $record->quantity }} · {{ $record->is_active ? 'Đang bán' : 'Ngừng bán' }}
                    @elseif ($resource === 'promotions')Giảm {{ $record->discount_percent }}% · {{ $record->is_active ? 'Đang bật' : 'Đã tắt' }} · {{ $record->starts_at->format('d/m/Y') }}–{{ $record->ends_at->format('d/m/Y') }}
                    @elseif ($resource === 'posts'){{ $record->status === 'PUBLISHED' ? 'Đã xuất bản' : 'Bản nháp' }}
                    @else—@endif
                </td>
                <td><div class="inline-form">
                    <a class="text-action" href="{{ route('admin.resources.edit', ['resource' => $resource, 'record' => $record->id]) }}">Sửa</a>
                    <form method="POST" action="{{ route('admin.resources.destroy', ['resource' => $resource, 'record' => $record->id]) }}" data-confirm="{{ $resource === 'categories' ? 'Xóa danh mục này?' : 'Ngừng sử dụng / ẩn mục này?' }}">
                        @csrf @method('DELETE')<button type="submit" class="text-action">{{ $resource === 'categories' ? 'Xóa' : 'Ngừng / ẩn' }}</button>
                    </form>
                </div></td>
            </tr>
        @empty<tr><td colspan="4">Chưa có dữ liệu phù hợp.</td></tr>@endforelse</tbody>
    </table></div>
    <x-pagination :paginator="$records" />
</section>
@endsection
