<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ForexPriceAlertController;

Route::get('/', function () {
    return view('welcome');
});


// --- Routes สำหรับหน้าที่ไม่มี Logic ซับซ้อน สามารถเรียก View ตรงๆ ได้ ---
Route::get('/login', function() {
    return view('login');
})->name('login');

Route::get('/register', function() {
    return view('register');
})->name('register');

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
Route::get('/community/single-post/{id}', [CommunityController::class, 'singlePost'])->name('community.single-post');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/create', [ArticleController::class, 'create'])->name('articles.create');
Route::get('/article/{id}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/post/{id}', [ArticleController::class, 'show'])->name('posts.show');
Route::get('/novel/create', [NovelController::class, 'create'])->name('novel.create');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
// Route::get('/dashboard', function() {
//     return view('dashboard');
// })->name('dashboard');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


// Route::get('/forex-price-alert', [ForexPriceAlertController::class, 'index'])->name('forex-price-alert.index');
// Route::post('/forex-price-alert/store', [ForexPriceAlertController::class, 'store'])->name('forex-price-alert.store');
