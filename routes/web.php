<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Uretim\TechnicalDrawingController;
use App\Http\Controllers\FeedbackActionController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

Route::get('/deneme', function (Request $request) {

  $path = Storage::disk('fotolar_disk'); 
  $files = File::files($path);

  foreach ($files as $file) {
    echo $file->getFilename() . '<br>';
  }
});

Route::middleware('auth')->get('/photo/{name}', [PhotoController::class, 'show'])->name('photo.show');

// Teknik resim: code query param ile (fallback'tan önce tanımlanmalı)
Route::get('/teknik-resim', [TechnicalDrawingController::class, 'show'])->name('teknik-resim');

// Geri bildirim e-posta aksiyonları (imzalı linkler)
Route::middleware('signed')->group(function () {
    Route::get('/feedback/onayla/{id}', [FeedbackActionController::class, 'showOnay'])->name('hata.onayla');
    Route::get('/feedback/reddet/{id}', [FeedbackActionController::class, 'showRet'])->name('hata.ret');
    Route::post('/feedback/islem/{id}', [FeedbackActionController::class, 'submit'])->name('hata.islem');
});

// SPA fallback
Route::get('{any?}', function () {
    return view('application');
})->where('any', '.*');
