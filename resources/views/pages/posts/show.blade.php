@extends('layouts.app')
@section('title', $post->title)
@section('content')
<article class="site-container page-stack article-body">
    <header class="page-title">
        <div class="section-title"><h1>{{ $post->title }}</h1></div>
        <x-breadcrumb :items="[['label' => 'Tin tức sức khỏe', 'href' => route('posts.index')], ['label' => $post->title]]" />
    </header>
    <p>{{ $post->published_at->format('d/m/Y') }}</p>
    @if ($post->cover_url)<img class="article-cover" src="{{ $post->cover_url }}" alt="">@endif
    <div class="article-content">{{ $post->content }}</div>
</article>
@endsection
