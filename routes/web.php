<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FacilityController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// Halaman Publik
// ---------------------------------------------------------------
Route::get('/', [FacilityController::class, 'home'])->name('home');

Route::get('/fasilitas', [FacilityController::class, 'index'])->name('facilities.index');
Route::get('/fasilitas/{facility}', [FacilityController::class, 'show'])->name('facilities.show');

Route::get('/kategori/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// ---------------------------------------------------------------
// Panel Admin
// ---------------------------------------------------------------
Route::prefix('admin')->name('admin.')->group(function () {

    // Kategori
    Route::get('/kategori', [CategoryController::class, 'adminIndex'])->name('categories.index');
    Route::get('/kategori/baru', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/kategori/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::get('/kategori/{category}/ubah', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/kategori/{category}/toggle-aktif', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');
    Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Fasilitas
    Route::get('/fasilitas', [FacilityController::class, 'adminIndex'])->name('facilities.index');
    Route::get('/fasilitas/baru', [FacilityController::class, 'create'])->name('facilities.create');
    Route::post('/fasilitas', [FacilityController::class, 'store'])->name('facilities.store');
    Route::get('/fasilitas/{facility}/ubah', [FacilityController::class, 'edit'])->name('facilities.edit');
    Route::put('/fasilitas/{facility}', [FacilityController::class, 'update'])->name('facilities.update');
    Route::delete('/fasilitas/{facility}', [FacilityController::class, 'destroy'])->name('facilities.destroy');
});
