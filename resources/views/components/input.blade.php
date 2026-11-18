@props(['id', 'label', 'type' => 'text', 'placeholder' => null, 'value' => null, 'name' => null, 'autocomplete' => null, 'required' => false, 'hint' => null, 'showError' => false])
@php
    $error = $showError && $name ? $errors->first($name) : null;
    $describedBy = trim(($hint ? $id.'-hint' : '').' '.($error ? $id.'-error' : ''));
@endphp
<div {{ $attributes->class('ui-field') }}>
    <label for="{{ $id }}">{{ $label }}</label>
    <input id="{{ $id }}" type="{{ $type }}" placeholder="{{ $placeholder }}" value="{{ $value }}" @if ($name) name="{{ $name }}" @endif @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif @required($required)>
    @if ($hint)
        <p class="auth-field-hint" id="{{ $id }}-hint">{{ $hint }}</p>
    @endif
    @if ($error)
        <p class="auth-field-error" id="{{ $id }}-error" role="alert">{{ $error }}</p>
    @endif
</div>
