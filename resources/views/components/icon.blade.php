@props(['name', 'size' => null])
<img src="{{ asset('images/figma/'.$name.'.svg') }}" alt=""
    @if ($size) width="{{ $size }}" height="{{ $size }}" @endif
    {{ $attributes }}>
