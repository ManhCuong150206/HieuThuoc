@if ($statusMessage ?? session('status'))
    <p class="auth-success" role="status">{{ $statusMessage ?? session('status') }}</p>
@endif
