@extends('layouts.app')
@section('title', $post->title)
@section('content')
@php($references = config('health_articles')[$post->cover_url] ?? null)
<article class="site-container page-stack article-body">
    <header class="page-title">
        <div class="section-title"><h1>{{ $post->title }}</h1></div>
        <x-breadcrumb :items="[['label' => 'Tin tức sức khỏe', 'href' => route('posts.index')], ['label' => $post->title]]" />
    </header>
    <p>{{ $post->published_at->format('d/m/Y') }}</p>
    @if ($post->cover_url)
        <img class="article-cover" src="{{ $post->cover_url }}" alt="{{ $references['photo_alt'] ?? '' }}">
    @endif
    <div class="article-content">{{ $post->content }}</div>
    @if ($references)
        <aside class="article-references" aria-label="Nguồn tham khảo">
            <p>Nguồn nội dung · <a class="link" href="{{ $references['source_url'] }}">{{ $references['source_name'] }}</a></p>
            <p>Ảnh minh họa · <a class="link" href="{{ $references['photo_url'] }}">{{ $references['photo_credit'] }}</a></p>
        </aside>
    @endif
</article>
@endsection
