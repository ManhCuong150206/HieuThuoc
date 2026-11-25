@extends('layouts.admin')
@section('title', ($record->exists ? 'Sửa ' : 'Thêm ').$title)
@section('toast-feedback', 'true')
@section('admin-content')
<header class="admin-heading"><x-section-title :title="($record->exists ? 'Sửa ' : 'Thêm ').$title" :level="1" /><x-button variant="outline" :href="route('admin.resources.index', $resource)">Quay lại</x-button></header>
<form method="POST" action="{{ $record->exists ? route('admin.resources.update', ['resource' => $resource, 'record' => $record->id]) : route('admin.resources.store', $resource) }}" class="admin-panel admin-form"
    @if ($resource === 'products') enctype="multipart/form-data" @endif>
    @csrf @if ($record->exists) @method('PUT') @endif
    @include('pages.admin.resource-fields')
    <div><x-button type="submit">Lưu thông tin</x-button></div>
</form>
@endsection
