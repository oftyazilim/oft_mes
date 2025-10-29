<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Uretim\TechnicalDrawingController;
use App\Http\Controllers\FeedbackActionController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

Route::get('/deneme', function (Request $request) {
    $disk = Storage::disk('public');

    // Ana dizin yolu
    $relativePath = 'kk-fotolari';
    $path = $disk->path($relativePath);

    // Dosyaları al
    $files = File::files($path);

    // Liste oluştur
    $data = collect($files)->map(function ($file) use ($disk, $relativePath) {
        $filename = $file->getFilename();
        $url = $disk->url($relativePath . '/' . $filename);

        return [
            'name' => $filename,
            'url' => $url,
        ];
    });

    return response()->json([
        'files' => $data,
    ]);
});


// Route::get('/deneme', function (Request $request) {

//     $path = Storage::disk('fotolar_disk')->path('fotolar/kk-fotolari/IEN-25017682');
//     //return 
//     $files = File::files($path);

//     $data = [];

//     foreach ($files as $file) {
//         $data[] = $file->getFilename();
//     }

//     return response()->json([
//         'files' => $files,
//         'data' => $data,
//     ]);
// });

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
