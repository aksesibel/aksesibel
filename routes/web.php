<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccessibilityTypeController;
use App\Http\Controllers\FacilityAccessibilityController;
use App\Http\Controllers\AccessibilityInspectionController;

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

/*
|--------------------------------------------------------------------------
| PBI-05: Kelola Fasilitas Aksesibilitas
|--------------------------------------------------------------------------
*/
Route::resource('facility-accessibilities', FacilityAccessibilityController::class);

/*
|--------------------------------------------------------------------------
| PBI-06: Kelola Pemeriksaan Aksesibilitas
|--------------------------------------------------------------------------
*/
Route::post(
    'facility-accessibilities/{facility_accessibility}/inspections',
    [AccessibilityInspectionController::class, 'store']
)->name('facility-accessibilities.inspections.store');