@extends('layouts.admin')
@section('title', 'Khách hàng')
@section('admin-content')
<x-section-title title="Tài khoản khách hàng" :level="1" />
<section class="admin-panel"><div class="admin-table-scroll"><table class="data-table">
    <thead><tr><th>Tài khoản</th><th>Thông tin</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
    <tbody>@forelse ($accounts as $account)
        <tr><td>{{ $account->username }} · {{ $account->role }}</td><td>{{ $account->profile?->full_name }}<br>{{ $account->profile?->email }}<br>{{ $account->profile?->phone }}</td><td>{{ $account->is_active ? 'Hoạt động' : 'Đã khóa' }}</td>
            <td>@if(strtolower($account->role) !== 'admin')<form method="POST" action="{{ route('admin.customers.update', $account) }}">@csrf @method('PUT')<input type="hidden" name="is_active" value="{{ $account->is_active ? 0 : 1 }}"><x-button type="submit" variant="outline">{{ $account->is_active ? 'Khóa' : 'Mở khóa' }}</x-button></form>@endif</td>
        </tr>
    @empty<tr><td colspan="4">Chưa có tài khoản.</td></tr>@endforelse</tbody>
</table></div><x-pagination :paginator="$accounts" /></section>
@endsection
