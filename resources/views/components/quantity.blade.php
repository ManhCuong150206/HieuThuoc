@props(['label' => 'Số lượng'])
<x-button variant="outline" aria-label="{{ $label }}: 1" {{ $attributes->class('quantity') }}>
    <span aria-hidden="true">−</span><span>1</span><span aria-hidden="true">+</span>
</x-button>
