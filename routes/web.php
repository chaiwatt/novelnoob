<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\SocialiteController;
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
Route::get('/community/single-post/{post}', [CommunityController::class, 'show'])->name('posts.show');
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
    Route::post('/admin/users/update-status', [AdminDashboardController::class, 'updateUserStatus'])->name('admin.users.updateStatus');
    Route::get('/admin/users/search', [AdminDashboardController::class, 'searchUsers'])->name('admin.users.search');
     // ⭐️ ROUTE ใหม่: สำหรับลบ Report (AJAX)
    Route::delete('/admin/reports/{report}', [AdminDashboardController::class, 'destroyReport'])->name('admin.reports.destroy');
    
    // ⭐️ ROUTE ใหม่: สำหรับลบ Post (AJAX)
    Route::delete('/admin/posts/{post}', [AdminDashboardController::class, 'destroyPost'])->name('admin.posts.destroy');
});

// Route สำหรับ Writer เท่านั้น
Route::middleware(['auth', 'writer'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/novel/{novel}/edit', [NovelController::class, 'edit'])->name('novel.edit');
    Route::get('/novel/{novel}/download', [NovelGenerationController::class, 'downloadTxt'])->name('novel.download');
    Route::post('/purchase-credits', [DashboardController::class, 'purchaseCredits'])->name('credits.purchase');
    Route::get('/credits/checkout/{package}', [DashboardController::class, 'showCheckout'])->name('credits.checkout');
    Route::post('/dashboard/settings/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.settings.update.profile');
    Route::post('/dashboard/settings/password', [DashboardController::class, 'updatePassword'])->name('dashboard.settings.update.password');
    Route::post('/dashboard/review/submit', [DashboardController::class, 'submitReview'])->name('dashboard.review.submit');
    Route::post('/community/posts', [CommunityController::class, 'store'])->name('community.posts.store');
    Route::patch('/community/posts/{post}', [CommunityController::class, 'update'])->name('community.posts.update');
    Route::delete('/community/posts/{post}', [CommunityController::class, 'destroy'])->name('community.posts.destroy');
    // --- NEW: Comment Routes ---
    // สร้างคอมเมนต์ (ใช้ 'auth' ก็พอ ทุกคนคอมเมนต์ได้)
    Route::post('/community/posts/{post}/comments', [CommunityController::class, 'storeComment'])->name('community.comments.store');

    // แก้ไขคอมเมนต์ (ใน Controller จะเช็คสิทธิ์ว่าเป็นเจ้าของ)
    Route::patch('/community/comments/{comment}', [CommunityController::class, 'updateComment'])->name('community.comments.update');
         
    // ลบคอมเมนต์ (ใน Controller จะเช็คสิทธิ์ว่าเป็นเจ้าของคอมเมนต์ หรือ เจ้าของโพสต์)
    Route::delete('/community/comments/{comment}', [CommunityController::class, 'destroyComment'])->name('community.comments.destroy');

    // --- NEW: Block User Route ---
    // บล็อกผู้ใช้ (ต้อง Login)
    Route::post('/community/users/{user}/block', [CommunityController::class, 'blockUser'])->name('community.users.block');

        // --- NEW: Reaction & Useful Routes ---
    // กด Reaction (ถูกใจ, รักเลย ฯลฯ)
    Route::post('/community/posts/{post}/react', [CommunityController::class, 'toggleReaction'])->name('community.posts.react');

    // กดมีประโยชน์
    Route::post('/community/posts/{post}/useful', [CommunityController::class, 'toggleUseful'])->name('community.posts.useful');

    Route::post('/community/posts/{post}/report', [CommunityController::class, 'storeReport'])->name('community.posts.report');
    
});


Route::post('/generate-plot', [NovelGenerationController::class, 'generatePlot']);
Route::post('/generate-outline', [NovelGenerationController::class, 'generateOutline']);

Route::post('/write-chapter/{chapter}', [NovelGenerationController::class, 'writeChapter'])->name('chapters.write');
Route::patch('/update-chapter/{chapter}', [NovelGenerationController::class, 'updateChapter'])->name('chapters.update');


Auth::routes();

// Route ที่ใช้ในการนำผู้ใช้ไปยังหน้า Login/Consent ของ Google
Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');

// Route ที่ Google จะเรียกกลับมา (Callback URL) พร้อมข้อมูลผู้ใช้
Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);

