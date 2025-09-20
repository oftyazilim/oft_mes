<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Uretim\TechnicalDrawingController;

// Teknik resim: code query param ile (fallback'tan önce tanımlanmalı)
Route::get('/teknik-resim', [TechnicalDrawingController::class, 'show'])->name('teknik-resim');

// SPA fallback
Route::get('{any?}', function () {
    return view('application');
})->where('any', '.*');
