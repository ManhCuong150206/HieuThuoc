@props(['entry', 'action', 'anchor'])
@php
    $status = session('discussion_status');
    $message = ($status['id'] ?? null) === $entry->id ? $status['message'] : null;
    $error = $errors->getBag('reply'.$entry->id)->first('admin_reply');
    $canReply = auth()->user()?->isAdmin();
@endphp
@if ($entry->admin_reply !== null || $canReply)
    <div class="discussion-reply" data-discussion-reply @if ($entry->admin_reply === null) hidden @endif>
        <strong>Phản hồi từ An Tâm Pharmacy</strong>
        <p class="discussion-text" data-discussion-reply-text>{{ $entry->admin_reply }}</p>
    </div>
@endif
@if ($message || $canReply)
    <p class="discussion-status" role="status" data-discussion-reply-status @unless ($message) hidden @endunless>{{ $message }}</p>
@endif
@if ($canReply)
    <details class="discussion-composer" @if ($error) open @endif>
        <summary>{{ $entry->admin_reply !== null ? 'Sửa câu trả lời' : 'Trả lời' }}</summary>
        <form class="discussion-form" method="POST" action="{{ $action }}" data-discussion-form="reply">
            @csrf
            @method('PUT')
            <label class="ui-field" for="{{ $anchor }}-reply">
                <span>Nội dung trả lời</span>
                <textarea id="{{ $anchor }}-reply" name="admin_reply" rows="3" maxlength="500" required aria-describedby="{{ $anchor }}-error" @if ($error) aria-invalid="true" @endif>{{ $error ? old('admin_reply') : $entry->admin_reply }}</textarea>
            </label>
            <p class="discussion-error" id="{{ $anchor }}-error" role="alert" data-discussion-error @unless ($error) hidden @endunless>{{ $error }}</p>
            <x-button type="submit">{{ $entry->admin_reply !== null ? 'Lưu câu trả lời' : 'Gửi câu trả lời' }}</x-button>
        </form>
    </details>
@endif
