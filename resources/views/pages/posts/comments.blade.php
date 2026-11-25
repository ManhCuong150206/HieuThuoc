<section class="health-comments" id="binh-luan" aria-labelledby="post-comments-title">
    <h2 id="post-comments-title">Bình luận <span data-discussion-count @if (!$comments->total()) hidden @endif>({{ $comments->total() }})</span></h2>
    @auth
        <form class="discussion-form" method="POST" action="{{ route('posts.comments.store', $post) }}" data-discussion-form="comment">
            @csrf
            @php($error = $errors->getBag('postComment')->first('body'))
            <label class="ui-field" for="post-comment-body">
                <span>Nội dung bình luận</span>
                <textarea id="post-comment-body" name="body" rows="3" maxlength="500" required aria-describedby="post-comment-error" @if ($error) aria-invalid="true" @endif>{{ old('body') }}</textarea>
            </label>
            <p class="discussion-error" id="post-comment-error" role="alert" data-discussion-error @unless ($error) hidden @endunless>{{ $error }}</p>
            <x-button type="submit">Gửi bình luận</x-button>
        </form>
    @else
        <p><a class="text-action" href="{{ route('login') }}">Đăng nhập</a> để bình luận.</p>
    @endauth
    <p class="discussion-status" role="status" data-discussion-comment-status hidden></p>
    <div class="discussion-results" data-discussion-results>
        @include('pages.posts.comment-list')
    </div>
</section>
