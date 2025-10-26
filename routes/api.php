<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForexPriceAlertController;
use App\Http\Controllers\NovelGenerationController;

// ย้าย Route ทั้งสองมาไว้ที่นี่
Route::get('/forex-price-alert', [ForexPriceAlertController::class, 'index']);
Route::post('/forex-price-alert/store', [ForexPriceAlertController::class, 'store']);
Route::delete('/forex-price-alert/{id}', [ForexPriceAlertController::class, 'destroy']);


// Route::post('/generate-plot', [NovelGenerationController::class, 'generatePlot']);
// Route::post('/generate-outline', [NovelGenerationController::class, 'generateOutline']);

// Route::post('/write-chapter/{chapter}', [NovelGenerationController::class, 'writeChapter'])->name('chapters.write');
// Route::patch('/update-chapter/{chapter}', [NovelGenerationController::class, 'updateChapter'])->name('chapters.update');



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/myfxbook-sentiment', [MyFxbookController::class, 'getSentiment']);//->middleware('check.myfxbook.api.token');
Route::post('/get-breakout-count', [MyFxbookController::class, 'getBreakoutCount']);//->middleware('check.myfxbook.api.token');
Route::post('/myfxbook-batch-sentiment', [MyFxbookController::class, 'getBatchSentiment']);