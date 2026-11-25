<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/quen-mat-khau', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/quen-mat-khau', [PasswordResetController::class, 'send'])->middleware('throttle:5,1')->name('password.email');
Route::get('/dat-lai-mat-khau', [PasswordResetController::class, 'resetForm'])->name('password.reset');
Route::post('/dat-lai-mat-khau/xac-minh', [PasswordResetController::class, 'verify'])->middleware('throttle:10,1')->name('password.verify');
Route::post('/dat-lai-mat-khau', [PasswordResetController::class, 'reset'])->middleware('throttle:10,1')->name('password.update');

Route::get('/dang-nhap', AuthController::class)->name('login');
Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.submit');
Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
