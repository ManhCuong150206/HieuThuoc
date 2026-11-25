@extends('layouts.app')
@section('title', 'Tin tức sức khỏe')
@section('description', 'Các bài hướng dẫn chăm sóc sức khỏe tại nhà từ An Tâm Pharmacy.')
@section('content')
<div class="site-container news-index">
    <header class="news-masthead" data-enter>
        <x-breadcrumb :items="[['label' => 'Tin tức sức khỏe']]" />
        <div class="news-heading-row">
            <h1>Tin tức sức khỏe</h1>
            <span>{{ $posts->total() }} bài viết</span>
        </div>
    </header>

    <div class="news-stories">
        @forelse ($posts as $post)
            @php
                $guide = $post->healthGuide();
                $featured = $loop->first && $posts->onFirstPage();
            @endphp
            <article @class(['news-story', 'news-story--lead' => $featured, 'news-story--text' => !$post->cover_url])>
                <a class="news-story-link" href="{{ route('posts.show', $post) }}" aria-labelledby="news-title-{{ $post->id }}">
                    @if ($post->cover_url)
                        <div class="news-story-image">
                            <img src="{{ $post->cover_url }}" alt="" loading="{{ $featured ? 'eager' : 'lazy' }}">
                        </div>
                    @endif
                    <div class="news-story-copy">
                        <div class="news-meta">
                            @if ($guide)<span>{{ $guide['topic'] }}</span>@endif
                            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d/m/Y') }}</time>
                        </div>
                        <h2 id="news-title-{{ $post->id }}">{{ $post->title }}</h2>
                        <p>{{ $guide['summary'] ?? \Illuminate\Support\Str::limit($post->content, 155, preserveWords: true) }}</p>
                        <span class="news-read">Đọc bài viết <span aria-hidden="true">→</span></span>
                    </div>
                </a>
            </article>
        @empty
            <p>Chưa có bài viết được xuất bản.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$posts" />
</div>
@endsection
