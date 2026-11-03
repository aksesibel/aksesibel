<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccessibilityTypeController;


Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| PBI-04: Kelola Jenis Aksesibilitas
|--------------------------------------------------------------------------
*/
Route::resource('accessibility-types', AccessibilityTypeController::class)
    ->only(['index']);
