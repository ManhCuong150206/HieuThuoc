<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, OrderItem $item, ReviewService $reviews): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['rating' => ['required', 'integer', 'between:1,5'], 'comment' => ['nullable', 'string', 'max:500']]);
        $reviews->create($request->user(), $item, $data);

        $message = 'Đánh giá đã được hiển thị.';

        return $request->expectsJson() ? response()->json(['message' => $message], 201) : back()->with('status', $message);
    }
}
