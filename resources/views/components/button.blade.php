@props(['href' => null, 'variant' => 'primary', 'type' => 'button'])
@php
    // One button pair for the whole site: the filled primary and one secondary look ("outline" and "soft" are the secondary).
    $classes = ['btn', $variant === 'primary' ? 'btn--primary' : 'btn--secondary', 'ui-button', 'ui-button--'.$variant];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
