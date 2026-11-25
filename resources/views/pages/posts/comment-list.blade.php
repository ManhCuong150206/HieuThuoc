@forelse ($comments as $comment)
    @php($anchor = 'binh-luan-'.$comment->id)
    <article class="discussion-entry" id="{{ $anchor }}">
        <header>
            <h3>{{ $comment->authorName() }}</h3>
            <time datetime="{{ $comment->created_at->toDateString() }}">{{ $comment->created_at->format('d/m/Y') }}</time>
        </header>
        <p class="discussion-text">{{ $comment->body }}</p>
        <x-discussion-reply :entry="$comment" :action="route('admin.posts.comments.reply', [$post, $comment])" :anchor="$anchor" />
    </article>
@empty
    <p>Chưa có bình luận.</p>
@endforelse
<x-pagination :paginator="$comments" />
