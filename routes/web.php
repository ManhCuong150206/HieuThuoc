<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminResourceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('products.show');
Route::get('/danh-muc/{category}', CategoryController::class)->whereNumber('category')->name('categories.show');
Route::get('/bai-viet', [PostController::class, 'index'])->name('posts.index');
Route::get('/bai-viet/{post}', [PostController::class, 'show'])->whereNumber('post')->name('posts.show');

Route::view('/huong-dan', 'pages.help')->name('help');
Route::get('/quen-mat-khau', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/quen-mat-khau', [PasswordResetController::class, 'send'])->middleware('throttle:5,1')->name('password.email');
Route::get('/dat-lai-mat-khau/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
Route::post('/dat-lai-mat-khau', [PasswordResetController::class, 'reset'])->middleware('throttle:10,1')->name('password.update');

Route::middleware(['auth', 'auth.session'])->group(function (): void {
    Route::get('/gio-hang', CartController::class)->name('cart.index');
    Route::post('/gio-hang/san-pham/{product}', [CartController::class, 'store'])->whereNumber('product')->name('cart.store');
    Route::put('/gio-hang/{item}', [CartController::class, 'update'])->whereNumber('item')->name('cart.update');
    Route::delete('/gio-hang/{item}', [CartController::class, 'destroy'])->whereNumber('item')->name('cart.destroy');
    Route::delete('/gio-hang', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/dat-hang-thanh-cong/{order?}', [CheckoutController::class, 'success'])->whereNumber('order')->name('checkout.success');
    Route::post('/don-hang/{order}/huy', [CheckoutController::class, 'cancel'])->whereNumber('order')->name('orders.cancel');
    Route::post('/don-hang/{order}/dat-lai', [CheckoutController::class, 'reorder'])->whereNumber('order')->name('orders.reorder');
    Route::get('/tai-khoan', AccountController::class)->name('account.index');
    Route::put('/tai-khoan', [AccountController::class, 'update'])->name('account.update');
    Route::put('/tai-khoan/mat-khau', [AccountController::class, 'password'])->middleware('throttle:10,1')->name('account.password');
    Route::get('/yeu-thich', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/yeu-thich/{product}', [FavoriteController::class, 'store'])->whereNumber('product')->name('favorites.store');
    Route::delete('/yeu-thich/{product}', [FavoriteController::class, 'destroy'])->whereNumber('product')->name('favorites.destroy');
    Route::post('/danh-gia/{item}', [ReviewController::class, 'store'])->whereNumber('item')->name('reviews.store');

    Route::prefix('admin')->name('admin.')->middleware(EnsureAdmin::class)->group(function (): void {
        Route::get('/', AdminController::class)->name('dashboard');
        Route::get('/don-hang', [AdminController::class, 'orders'])->name('orders.index');
        Route::put('/don-hang/{order}', [AdminController::class, 'updateOrder'])->whereNumber('order')->name('orders.update');
        Route::get('/khach-hang', [AdminController::class, 'customers'])->name('customers.index');
        Route::put('/khach-hang/{account}', [AdminController::class, 'updateCustomer'])->whereNumber('account')->name('customers.update');
        Route::get('/danh-gia', [AdminController::class, 'reviews'])->name('reviews.index');
        Route::put('/danh-gia/{review}', [AdminController::class, 'updateReview'])->whereNumber('review')->name('reviews.update');
        Route::get('/bao-cao', [AdminController::class, 'report'])->name('report');
        Route::prefix('quan-ly/{resource}')->where(['resource' => 'products|categories|promotions|posts'])->name('resources.')->group(function (): void {
            Route::get('/', [AdminResourceController::class, 'index'])->name('index');
            Route::get('/tao', [AdminResourceController::class, 'form'])->name('create');
            Route::get('/{record}/sua', [AdminResourceController::class, 'form'])->whereNumber('record')->name('edit');
            Route::post('/', [AdminResourceController::class, 'store'])->name('store');
            Route::put('/{record}', [AdminResourceController::class, 'update'])->whereNumber('record')->name('update');
            Route::delete('/{record}', [AdminResourceController::class, 'destroy'])->whereNumber('record')->name('destroy');
        });
    });
});

Route::get('/dang-nhap', AuthController::class)->name('login');
Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.submit');
Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
