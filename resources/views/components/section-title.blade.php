@props(['title', 'eyebrow' => null, 'description' => null, 'level' => 2])
<div {{ $attributes->class('section-title') }}>
    @if ($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
    @if ($level === 1)<h1>{{ $title }}</h1>@else<h2>{{ $title }}</h2>@endif
    @if ($description)<p class="section-description">{{ $description }}</p>@endif
</div>
