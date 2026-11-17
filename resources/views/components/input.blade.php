@props(['id', 'label', 'type' => 'text', 'placeholder' => null, 'value' => null])
<div {{ $attributes->class('ui-field') }}>
    <label for="{{ $id }}">{{ $label }}</label>
    <input id="{{ $id }}" type="{{ $type }}" placeholder="{{ $placeholder }}" value="{{ $value }}">
</div>
