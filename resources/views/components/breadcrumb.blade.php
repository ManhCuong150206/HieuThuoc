@props(['items'])
<nav class="breadcrumb" aria-label="Đường dẫn">
    <ol>
        <li><a href="{{ route('home') }}">Trang chủ</a></li>
        @foreach ($items as $item)
            <li>
                <span aria-hidden="true">/</span>
                @if (isset($item['href']))
                    <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                @else
                    <span aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
