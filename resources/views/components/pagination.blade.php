@props(['paginator'])
@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Phân trang">
        @if ($paginator->previousPageUrl())<x-button variant="outline" :href="$paginator->previousPageUrl()" aria-label="Trang trước">←</x-button>@endif
        <span>Trang {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())<x-button variant="outline" :href="$paginator->nextPageUrl()" aria-label="Trang sau">→</x-button>@endif
    </nav>
@endif
