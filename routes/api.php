<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForexPriceAlertController;

// ย้าย Route ทั้งสองมาไว้ที่นี่
Route::get('/forex-price-alert', [ForexPriceAlertController::class, 'index']);
Route::post('/forex-price-alert/store', [ForexPriceAlertController::class, 'store']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
