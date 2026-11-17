<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::controller(ProductController::class)->prefix('san-pham')->name('products.')->group(function (): void {
    Route::get('/', 'index')->name('index');
    Route::get('/{slug}', 'show')->name('show');
});

Route::get('/danh-muc/{slug}', CategoryController::class)->name('categories.show');
Route::get('/gio-hang', CartController::class)->name('cart.index');

Route::controller(CheckoutController::class)->name('checkout.')->group(function (): void {
    Route::get('/thanh-toan', 'index')->name('index');
    Route::get('/dat-hang-thanh-cong', 'success')->name('success');
});

Route::get('/tai-khoan', AccountController::class)->name('account.index');
Route::get('/quan-tri', AdminController::class)->name('admin.dashboard');
Route::get('/dang-nhap', AuthController::class)->name('login');
