<?php

use App\Enums\AdminResource;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscussionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(EnsureAdmin::class)->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/don-hang', [OrderController::class, 'index'])->name('orders.index');
    Route::put('/don-hang/{order}', [OrderController::class, 'update'])->whereNumber('order')->name('orders.update');
    Route::get('/khach-hang', [CustomerController::class, 'index'])->name('customers.index');
    Route::put('/khach-hang/{account}', [CustomerController::class, 'update'])->whereNumber('account')->name('customers.update');
    Route::get('/danh-gia', [DiscussionController::class, 'index'])->name('reviews.index');
    Route::delete('/danh-gia/{review}', [DiscussionController::class, 'destroyReview'])->whereNumber('review')->name('reviews.destroy');
    Route::put('/san-pham/{product}/danh-gia/{review}/tra-loi', [DiscussionController::class, 'replyToReview'])
        ->whereNumber(['product', 'review'])->name('reviews.reply');
    Route::put('/bai-viet/{post}/binh-luan/{comment}/tra-loi', [DiscussionController::class, 'replyToComment'])
        ->whereNumber(['post', 'comment'])->scopeBindings()->name('posts.comments.reply');
    Route::get('/bao-cao', [DashboardController::class, 'report'])->name('report');
    Route::patch('/quan-ly/products/cap-nhat-hang-loat', [ProductController::class, 'bulkUpdate'])->name('products.bulk-update');
    Route::delete('/quan-ly/products/{product}/xoa', [ProductController::class, 'destroy'])
        ->whereNumber('product')->name('products.destroy');
    Route::prefix('quan-ly/{resource}')->where(['resource' => implode('|', array_column(AdminResource::cases(), 'value'))])->name('resources.')->group(function (): void {
        Route::get('/', [ResourceController::class, 'index'])->name('index');
        Route::get('/tao', [ResourceController::class, 'form'])->name('create');
        Route::get('/{record}/sua', [ResourceController::class, 'form'])->whereNumber('record')->name('edit');
        Route::post('/', [ResourceController::class, 'store'])->name('store');
        Route::put('/{record}', [ResourceController::class, 'update'])->whereNumber('record')->name('update');
        Route::delete('/{record}', [ResourceController::class, 'destroy'])->whereNumber('record')->name('destroy');
    });
});
