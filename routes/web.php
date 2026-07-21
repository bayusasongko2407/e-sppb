<?php

use App\Http\Controllers\AttachmentController;
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
    Route::get('/sppb/{record}/preview', [SppbPreviewController::class, 'preview'])->name('sppb.preview');
});

Route::get('/attachments/{attachment:uuid}/preview', [AttachmentController::class, 'preview'])
    ->name('attachments.preview')
    ->middleware('signed');

Route::get('/attachments/{attachment:uuid}/download', [AttachmentController::class, 'download'])
    ->name('attachments.download')
    ->middleware('signed');

Route::get('/attachments/{attachment:uuid}/delete', [AttachmentController::class, 'delete'])
    ->name('attachments.delete')
    ->middleware('signed');
