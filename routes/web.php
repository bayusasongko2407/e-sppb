<?php

use App\Http\Controllers\DocumentVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/verify/document/{verificationUuid}/page/{page}', [DocumentVerificationController::class, 'verifyPublicPage'])
        ->name('document.verify');
});
