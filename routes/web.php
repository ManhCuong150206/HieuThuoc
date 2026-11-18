<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::controller(ProductController::class)->prefix('san-pham')->name('products.')->group(function (): void {
    Route::get('/', 'index')->name('index');
    Route::get('/{product}', 'show')->whereNumber('product')->name('show');
});

Route::get('/danh-muc/{category}', CategoryController::class)->whereNumber('category')->name('categories.show');
Route::get('/gio-hang', CartController::class)->middleware('auth')->name('cart.index');

Route::controller(CheckoutController::class)->name('checkout.')->group(function (): void {
    Route::get('/thanh-toan', 'index')->name('index');
    Route::get('/dat-hang-thanh-cong', 'success')->name('success');
});

Route::get('/tai-khoan', AccountController::class)->middleware('auth')->name('account.index');
Route::put('/tai-khoan', [AccountController::class, 'update'])->middleware('auth')->name('account.update');
Route::get('/admin', AdminController::class)->middleware(['auth', EnsureAdmin::class])->name('admin.dashboard');
Route::get('/dang-nhap', AuthController::class)->name('login');
Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
// GET: hiển thị form đăng ký. POST: kiểm tra và lưu tài khoản mới.
Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.submit');
Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
