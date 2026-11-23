@props(['title', 'eyebrow' => null, 'description' => null, 'level' => 2, 'id' => null])
<div {{ $attributes->class('section-title') }}>
    @if ($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
    @if ($level === 1)<h1 @if ($id) id="{{ $id }}" @endif>{{ $title }}</h1>@else<h2 @if ($id) id="{{ $id }}" @endif>{{ $title }}</h2>@endif
    @if ($description)<p class="section-description">{{ $description }}</p>@endif
</div>
