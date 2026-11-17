@props(['variant' => 'pale'])
<span {{ $attributes->class(['badge', 'badge--'.$variant]) }}>{{ $slot }}</span>
