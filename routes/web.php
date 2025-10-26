<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ForexPriceAlertController;
use App\Http\Controllers\NovelGenerationController;

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

// Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
Route::get('/community/single-post/{id}', [CommunityController::class, 'singlePost'])->name('community.single-post');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/create', [ArticleController::class, 'create'])->name('articles.create');
Route::get('/article/{id}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/post/{id}', [ArticleController::class, 'show'])->name('posts.show');
Route::get('/novel/create', [NovelController::class, 'create'])->name('novel.create');


// Route สำหรับ Admin เท่านั้น
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard.index');
        // Route สำหรับแสดงหน้า Dashboard (ที่เราเพิ่งแก้)
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard.index');
    
    // Route สำหรับรับฟอร์ม 'บันทึก'
    Route::put('/admin/packages/update', [AdminDashboardController::class, 'updatePackages'])->name('admin.packages.update');
});

// Route สำหรับ Writer เท่านั้น
Route::middleware(['auth', 'writer'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/novel/{novel}/edit', [NovelController::class, 'edit'])->name('novel.edit');
    Route::get('/novel/{novel}/download', [NovelGenerationController::class, 'downloadTxt'])->name('novel.download');
    Route::post('/purchase-credits', [DashboardController::class, 'purchaseCredits'])->name('credits.purchase');

    // เพิ่ม routes อื่นๆ ที่ writer ควรเข้าถึงได้ที่นี่
});


Route::post('/generate-plot', [NovelGenerationController::class, 'generatePlot']);
Route::post('/generate-outline', [NovelGenerationController::class, 'generateOutline']);

Route::post('/write-chapter/{chapter}', [NovelGenerationController::class, 'writeChapter'])->name('chapters.write');
Route::patch('/update-chapter/{chapter}', [NovelGenerationController::class, 'updateChapter'])->name('chapters.update');


Auth::routes();

