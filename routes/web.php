<?php

use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('products.show');
Route::get('/danh-muc/{category}', [ProductController::class, 'index'])->whereNumber('category')->name('categories.show');
Route::get('/bai-viet', [PostController::class, 'index'])->name('posts.index');
Route::get('/bai-viet/{post}', [PostController::class, 'show'])->whereNumber('post')->name('posts.show');

Route::middleware(['auth', 'auth.session'])->group(function (): void {
    Route::get('/gio-hang', CartController::class)->name('cart.index');
    Route::post('/gio-hang/san-pham/{product}', [CartController::class, 'store'])->whereNumber('product')->name('cart.store');
    Route::put('/gio-hang/{item}', [CartController::class, 'update'])->whereNumber('item')->name('cart.update');
    Route::delete('/gio-hang/{item}', [CartController::class, 'destroy'])->whereNumber('item')->name('cart.destroy');
    Route::delete('/gio-hang', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan/chon-san-pham', [CheckoutController::class, 'select'])->name('checkout.select');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/dat-hang-thanh-cong/{order?}', [CheckoutController::class, 'success'])->whereNumber('order')->name('checkout.success');
    Route::post('/don-hang/{order}/huy', [OrderController::class, 'cancel'])->whereNumber('order')->name('orders.cancel');
    Route::post('/don-hang/{order}/xac-nhan-da-nhan', [OrderController::class, 'confirmDelivery'])->whereNumber('order')->name('orders.confirm-delivery');
    Route::post('/don-hang/{order}/dat-lai', [OrderController::class, 'reorder'])->whereNumber('order')->name('orders.reorder');
    Route::get('/tai-khoan', ProfileController::class)->name('account.index');
    Route::get('/tai-khoan/don-hang', [OrderController::class, 'index'])->name('account.orders');
    Route::put('/tai-khoan', [ProfileController::class, 'update'])->name('account.update');
    Route::put('/tai-khoan/mat-khau', [ProfileController::class, 'password'])->middleware('throttle:10,1')->name('account.password');
    Route::post('/danh-gia/{item}', [ReviewController::class, 'store'])->whereNumber('item')->name('reviews.store');
    Route::post('/bai-viet/{post}/binh-luan', [PostCommentController::class, 'store'])->whereNumber('post')->middleware('throttle:10,1')->name('posts.comments.store');

    require __DIR__.'/admin.php';
});

require __DIR__.'/auth.php';
