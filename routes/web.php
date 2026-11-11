<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('verifikasi')->name('verifikasi.')->group(function () {
    Route::get('/antrean', [VerificationController::class, 'antrean'])
        ->name('antrean');
});
