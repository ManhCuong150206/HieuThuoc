@extends('layouts.app')
@section('title', 'Tin tức sức khỏe')
@section('content')
<div class="site-container page-stack">
    <header class="page-title">
        <x-section-title title="Tin tức sức khỏe" :level="1" />
    </header>
    <div class="articles-grid">
        @forelse ($posts as $post)
            <article class="article-card">
                @if ($post->cover_url)<img class="article-cover" src="{{ $post->cover_url }}" alt="" loading="lazy">@endif
                <h2><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h2>
                <p>{{ \Illuminate\Support\Str::limit($post->content, 150) }}</p>
                <a class="text-action" href="{{ route('posts.show', $post) }}">Đọc bài viết</a>
            </article>
        @empty<p>Chưa có bài viết được xuất bản.</p>@endforelse
    </div>
    <x-pagination :paginator="$posts" />
</div>
@endsection
