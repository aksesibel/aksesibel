<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacilityCategoryController;
use App\Http\Controllers\IssueCategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportingPeriodController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccessibilityTypeController;
use App\Http\Controllers\FacilityAccessibilityController;
use App\Http\Controllers\AccessibilityInspectionController;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:registration')->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'known-role'])->group(function (): void {
    Route::get('/account', [ProfileController::class, 'show'])->name('account');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::prefix('pengelola')->name('manage.')->middleware(['auth', 'can:manage-supporting-data'])->group(function (): void {
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics');
    Route::patch('/users/{user}/status', [UserController::class, 'status'])->name('users.status');
    Route::resource('users', UserController::class)->except('destroy');
    Route::resource('facility-categories', FacilityCategoryController::class);
    Route::resource('issue-categories', IssueCategoryController::class);
    Route::resource('reporting-periods', ReportingPeriodController::class);
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
Route::get(
    '/reports/facilities/{facility}/accessibilities',
    [ReportController::class, 'getFacilityAccessibilities']
)->name('reports.facilities.accessibilities');

// PBI-07 dan PBI-09: Kelola Laporan Masyarakat
Route::get('/reports', [ReportController::class, 'index'])
    ->name('reports.index');

Route::get('/reports/create', [ReportController::class, 'create'])
    ->name('reports.create');

Route::post('/reports', [ReportController::class, 'store'])
    ->name('reports.store');

Route::get('/reports/{report}', [ReportController::class, 'show'])
    ->name('reports.show');
