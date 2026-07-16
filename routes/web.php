<?php

use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\SppbPreviewController;
use Illuminate\Support\Facades\Route;

// Public document verification route — uses SHA256 token for security
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/verify/document/{sha256Token}', [DocumentVerificationController::class, 'verifyPublicPage'])
        ->name('document.verify')
        ->where('sha256Token', '[a-f0-9]{64}');
});

Route::middleware('auth')->group(function () {
    Route::get('/sppb/{id}/preview', [SppbPreviewController::class, 'preview'])->name('sppb.preview');
});
