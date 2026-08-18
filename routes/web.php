<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\GoodsReleasePreviewController;
use App\Http\Controllers\SppbPreviewController;
use Illuminate\Support\Facades\Route;

// Public document verification route — uses SHA256 token for security
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/verify/document/{sha256Token}', [DocumentVerificationController::class, 'verifyPublicPage'])
        ->name('document.verify')
        ->where('sha256Token', '[a-f0-9]{64}');
    Route::match(['get', 'post'], '/verify/document/{hash?}', [DocumentVerificationController::class, 'verifyDocument']);
});

// Public Privacy Policy route for Meta WhatsApp Business API & Web Crawler verification
Route::get('/privacy-policy', fn () => view('privacy-policy'))->name('privacy.policy');
Route::get('/kebijakan-privasi', fn () => view('privacy-policy'))->name('kebijakan.privasi');

// Public documentation / user manual
Route::get('/docs', fn () => view('docs'))->name('docs');

// Merged API docs page: OpenAPI Reference (Stoplight Elements) + Panduan Mobile
// Note: /docs/api is served by Scramble (OpenAPI UI) — our page at /docs/api-reference
//       embeds the same OpenAPI spec (/docs/api.json) but with the mobile guide alongside.
Route::get('/docs/api-reference', fn () => view('docs-api'))->name('docs.api');
Route::get('/docs/api.md', fn () => response(view('docs-api-md')->render(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']))->name('docs.api.md');

Route::middleware('auth')->group(function () {
    Route::get('/sppb/{record}/preview', [SppbPreviewController::class, 'preview'])->name('sppb.preview');
    Route::get('/goods-releases/{record}/preview', [GoodsReleasePreviewController::class, 'preview'])->name('goods-releases.preview');
});

Route::get('/attachments/{attachment:uuid}/viewer', [AttachmentController::class, 'viewer'])
    ->name('attachments.viewer')
    ->middleware('signed');

Route::get('/attachments/{attachment:uuid}/preview', [AttachmentController::class, 'preview'])
    ->name('attachments.preview')
    ->middleware('signed');

Route::get('/attachments/{attachment:uuid}/download', [AttachmentController::class, 'download'])
    ->name('attachments.download')
    ->middleware('signed');

Route::get('/attachments/{attachment:uuid}/delete', [AttachmentController::class, 'delete'])
    ->name('attachments.delete')
    ->middleware('signed');
