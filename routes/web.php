<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccessibilityTypeController;
use App\Http\Controllers\FacilityAccessibilityController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| PBI-04: Kelola Jenis Aksesibilitas
|--------------------------------------------------------------------------
*/
Route::resource('accessibility-types', AccessibilityTypeController::class)
    ->except(['show']);

Route::resource('facility-accessibilities', FacilityAccessibilityController::class);