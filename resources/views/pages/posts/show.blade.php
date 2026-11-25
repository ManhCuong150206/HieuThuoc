@extends('layouts.app')
@php
    $guide = $post->healthGuide();
    $photo = config('health_articles')[$post->cover_url] ?? null;
    $paragraphs = preg_split('/\R\s*\R/u', trim($post->content));
    $summary = $guide['summary'] ?? array_shift($paragraphs);
@endphp
@section('title', $post->title)
@section('description', $summary)
@section('content')
<article class="site-container health-article">
    <header class="health-head" data-enter>
        <div class="health-dateline">
            <x-breadcrumb :items="[['label' => 'Tin tức sức khỏe', 'href' => route('posts.index')]]" />
            <time datetime="{{ $post->published_at->toDateString() }}">Ngày {{ $post->published_at->format('d/m/Y') }}</time>
        </div>
        @if ($guide)<p class="health-topic">{{ $guide['topic'] }}</p>@endif
        <h1>{{ $post->title }}</h1>
        <p class="health-summary">{{ $summary }}</p>
    </header>

    @if ($post->cover_url)
        <div class="health-banner">
            <figure>
                <img src="{{ $post->cover_url }}" alt="{{ $photo['photo_alt'] ?? '' }}">
                @if ($guide)<figcaption>{{ $guide['caption'] }}</figcaption>@endif
            </figure>
            @if ($guide)
                <nav class="health-contents" aria-label="Mục lục bài viết">
                    <h2>Trong bài viết</h2>
                    <ol>
                        @foreach ($guide['sections'] as $section)
                            <li><a href="#muc-{{ $loop->iteration }}"><span aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>{{ $section['title'] }}</a></li>
                        @endforeach
                    </ol>
                </nav>
            @endif
        </div>
    @endif

    <div class="health-reading">
        @if ($guide)
            @foreach ($guide['sections'] as $section)
                <section class="health-section" id="muc-{{ $loop->iteration }}">
                    <h2><span class="health-number">{{ sprintf('%02d', $loop->iteration) }}</span>{{ $section['title'] }}</h2>
                    @if (isset($section['text']))<p>{{ $section['text'] }}</p>@endif
                    @if (isset($section['steps']))
                        <ol class="health-steps">
                            @foreach ($section['steps'] as $label => $instruction)
                                <li>
                                    <span class="health-step-label">Bước {{ $loop->iteration }}</span>
                                    <h3>{{ $label }}</h3>
                                    <p>{{ $instruction }}</p>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                    @if (!empty($section['formula']))
                        <div class="health-formula">
                            <math display="block" aria-label="Huyết áp được ghi theo thứ tự tâm thu trên tâm trương, đơn vị milimét thủy ngân">
                                <semantics>
                                    <mrow><mfrac><mtext>Tâm thu</mtext><mtext>Tâm trương</mtext></mfrac><mspace width=".5em"/><mtext>(mmHg)</mtext></mrow>
                                    <annotation encoding="application/x-tex">\frac{\text{Tâm thu}}{\text{Tâm trương}}\; (\mathrm{mmHg})</annotation>
                                </semantics>
                            </math>
                        </div>
                    @endif
                    @if (isset($section['rows']))
                        <table class="health-table">
                            @if (isset($section['table_caption']))<caption>{{ $section['table_caption'] }}</caption>@endif
                            <thead><tr>@foreach ($section['columns'] as $column)<th scope="col">{{ $column }}</th>@endforeach</tr></thead>
                            <tbody>
                                @foreach ($section['rows'] as $row)
                                    <tr>@foreach ($row as $value)<td>{{ $value }}</td>@endforeach</tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    @if (isset($section['items']))
                        <ul class="health-points">@foreach ($section['items'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                    @endif
                    @if (isset($section['after']))<p>{{ $section['after'] }}</p>@endif
                    @if (isset($section['note']))<p class="health-note">{{ $section['note'] }}</p>@endif
                </section>
            @endforeach
        @else
            @foreach ($paragraphs as $paragraph)<p class="health-plain">{{ $paragraph }}</p>@endforeach
        @endif

        <footer class="health-sources">
            @if ($guide)
                <h2>Nguồn tham khảo</h2>
                <a href="{{ $guide['source_url'] }}">{{ $guide['source_name'] }}</a>
                @foreach ($guide['extra_sources'] ?? [] as $name => $url)<a href="{{ $url }}">{{ $name }}</a>@endforeach
            @endif
            @if ($photo)<p>Ảnh minh họa · <a href="{{ $photo['photo_url'] }}">{{ $photo['photo_credit'] }}</a></p>@endif
            <a class="news-read" href="{{ route('posts.index') }}"><span aria-hidden="true">←</span> Tất cả tin tức sức khỏe</a>
        </footer>
        @include('pages.posts.comments')
    </div>
</article>
@endsection
