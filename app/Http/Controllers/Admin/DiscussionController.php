<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    public function index(Request $request): View
    {
        $showComments = $request->query('tab') === 'bai-viet';

        return view('pages.admin.reviews', [
            'showComments' => $showComments,
            'entries' => $showComments
                ? PostComment::with('post', 'account.profile')->orderByDesc('id')->paginate(20)->appends(['tab' => 'bai-viet'])
                : Review::with('orderItem.product', 'orderItem.order.user')->orderByDesc('id')->paginate(20),
        ]);
    }

    public function destroyReview(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Đã xóa đánh giá.');
    }

    public function replyToComment(Request $request, Post $post, PostComment $comment): RedirectResponse|JsonResponse
    {
        abort_unless($post->isPublished(), 404);

        $redirect = redirect()->route('posts.show', ['post' => $post, 'comment' => $comment->id])
            ->withFragment('binh-luan-'.$comment->id);

        return $this->saveReply($request, $comment, $redirect);
    }

    public function replyToReview(Request $request, Product $product, Review $review): RedirectResponse|JsonResponse
    {
        abort_unless($product->is_active && $review->isPubliclyVisible()
            && $review->orderItem?->product_id === $product->id, 404);

        $redirect = redirect()->route('products.show', ['product' => $product, 'review' => $review->id])
            ->withFragment('danh-gia-'.$review->id);

        return $this->saveReply($request, $review, $redirect);
    }

    private function saveReply(Request $request, PostComment|Review $entry, RedirectResponse $redirect): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), ['admin_reply' => ['required', 'string', 'max:500']], [
            'admin_reply.required' => 'Vui lòng nhập nội dung trả lời.',
            'admin_reply.max' => 'Câu trả lời tối đa 500 ký tự.',
        ]);
        if ($validator->fails()) {
            throw (new ValidationException($validator))->errorBag('reply'.$entry->id)
                ->redirectTo($redirect->getTargetUrl());
        }

        $entry->admin_reply = $validator->validated()['admin_reply'];
        $entry->save();

        $message = 'Đã lưu câu trả lời.';
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'reply' => $entry->admin_reply]);
        }

        return $redirect->with('discussion_status', ['id' => $entry->id, 'message' => $message]);
    }
}
