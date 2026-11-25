@extends('layouts.admin')
@section('title', $title)
@section('toast-feedback', 'true')
@section('admin-content')
<header class="admin-heading">
    <x-section-title :title="$title" :level="1" />
    <x-button data-admin-create-open aria-haspopup="dialog" aria-controls="resource-create-dialog">Thêm mới</x-button>
</header>
<x-admin.resource-dialog :resource="$resource" :title="$title" :record="$draft" :categories="$categories" :promotion-products="$promotionProducts" />
<form class="inline-form" method="GET"><label class="sr-only" for="admin-search">Tìm kiếm</label><input id="admin-search" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên" maxlength="100"><x-button type="submit" variant="outline">Tìm kiếm</x-button></form>
@include('pages.admin.resource-results')
@endsection
