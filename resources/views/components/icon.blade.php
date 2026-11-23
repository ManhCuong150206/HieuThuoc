@props(['name', 'size' => null])
@if (in_array($name, ['bag', 'search', 'user', 'account-user', 'success-check'], true))
    <svg width="{{ $size ?? 24 }}" height="{{ $size ?? 24 }}" aria-hidden="true" {{ $attributes->class('ui-icon') }}>
        <use href="{{ asset('images/ui-icons.svg').'#'.$name }}" />
    </svg>
@else
<img src="{{ asset('images/figma/'.$name.'.svg') }}" alt=""
    @if ($size) width="{{ $size }}" height="{{ $size }}" @endif
    {{ $attributes }}>
@endif
