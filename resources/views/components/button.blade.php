@props(['href' => null, 'variant' => 'primary'])
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['ui-button', 'ui-button--'.$variant]) }}>{{ $slot }}</a>
@else
    <button type="button" {{ $attributes->class(['ui-button', 'ui-button--'.$variant]) }}>{{ $slot }}</button>
@endif
