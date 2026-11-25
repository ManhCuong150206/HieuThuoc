<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PostCommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse|JsonResponse
    {
        abort_unless($post->isPublished(), 404);

        $validator = Validator::make($request->all(), ['body' => ['required', 'string', 'max:500']], [
            'body.required' => 'Vui lòng nhập nội dung bình luận.',
            'body.max' => 'Bình luận tối đa 500 ký tự.',
        ]);
        if ($validator->fails()) {
            throw (new ValidationException($validator))->errorBag('postComment')
                ->redirectTo(route('posts.show', $post).'#binh-luan');
        }

        $comment = $post->comments()->create($validator->validated() + ['account_id' => $request->user()->id]);
        $message = 'Đã gửi bình luận.';
        if ($request->expectsJson()) {
            $comments = $post->paginateComments($comment->id)->withPath(route('posts.show', $post))->fragment('binh-luan');

            return response()->json([
                'message' => $message,
                'total' => $comments->total(),
                'html' => view('pages.posts.comment-list', compact('post', 'comments'))->render(),
                'url' => route('posts.show', ['post' => $post, 'comment' => $comment->id]).'#binh-luan-'.$comment->id,
            ], 201);
        }

        return redirect()->route('posts.show', ['post' => $post, 'comment' => $comment->id])
            ->withFragment('binh-luan-'.$comment->id)
            ->with('discussion_status', ['id' => $comment->id, 'message' => $message]);
    }
}
