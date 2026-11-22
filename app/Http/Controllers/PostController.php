<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('pages.posts.index', ['posts' => Post::where('status', 'PUBLISHED')
            ->whereDate('published_at', '<=', today())->orderByDesc('published_at')->orderByDesc('id')->paginate(9)]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->status === 'PUBLISHED' && $post->published_at && $post->published_at->lte(today()), 404);

        return view('pages.posts.show', ['post' => $post]);
    }
}
