<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('pages.posts.index', ['posts' => Post::latestPublished()->paginate(9)]);
    }

    public function show(Request $request, Post $post): View
    {
        abort_unless($post->isPublished(), 404);

        $focus = filter_var($request->query('comment'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

        return view('pages.posts.show', [
            'post' => $post,
            'comments' => $post->paginateComments($focus)->fragment('binh-luan'),
        ]);
    }
}
