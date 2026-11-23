<?php

use App\Http\Controllers\AccessibilityInspectionController;
use App\Http\Controllers\AccessibilityTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FacilityAccessibilityController;
use App\Http\Controllers\FacilityCategoryController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FollowUpAssignmentController;
use App\Http\Controllers\FollowUpEvidenceController;
use App\Http\Controllers\FollowUpProgressController;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\IssueCategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportingPeriodController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// Halaman Publik
// ---------------------------------------------------------------
Route::get('/', [FacilityController::class, 'home'])->name('home');
Route::get('/fasilitas', [FacilityController::class, 'index'])->name('facilities.index');
Route::get('/fasilitas/{facility}', [FacilityController::class, 'show'])->name('facilities.show');
Route::get('/kategori/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// ---------------------------------------------------------------
// API — data publik (JSON)
// ---------------------------------------------------------------
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/wilayah', [RegionController::class, 'index'])->name('regions.index');
    Route::get('/wilayah/{region}', [RegionController::class, 'show'])->name('regions.show');
    Route::get('/fasilitas/peta', [FacilityController::class, 'mapData'])->name('facilities.map');
    Route::post('/geocode', GeocodeController::class)->middleware(['auth', 'can:manage-public-facilities'])->name('geocode');
});

// ---------------------------------------------------------------
// Autentikasi
// ---------------------------------------------------------------
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

// ---------------------------------------------------------------
// Panel Admin — kelola kategori & fasilitas (termasuk lokasi/wilayah)
// ---------------------------------------------------------------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:manage-public-facilities'])->group(function () {

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

// ---------------------------------------------------------------
// Ruang kerja Pengelola — data pendukung
// ---------------------------------------------------------------
Route::prefix('pengelola')->name('manage.')->middleware(['auth', 'can:manage-supporting-data'])->group(function (): void {
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics');
    Route::patch('/users/{user}/status', [UserController::class, 'status'])->name('users.status');
    Route::resource('users', UserController::class)->except('destroy');
    Route::resource('facility-categories', FacilityCategoryController::class);
    Route::resource('issue-categories', IssueCategoryController::class);
    Route::resource('reporting-periods', ReportingPeriodController::class);
});

Route::middleware(['auth', 'can:manage-supporting-data'])->group(function (): void {
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
});

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

Route::prefix('verifikasi')->name('verifikasi.')->middleware(['auth', 'role:Admin'])->group(function (): void {
    Route::get('/antrean', [VerificationController::class, 'antrean'])
        ->name('antrean');

    Route::get('/riwayat', [VerificationController::class, 'riwayat'])
        ->name('riwayat');

    Route::get('/status', [VerificationController::class, 'kelolaStatus'])
        ->name('status');

    Route::post('/status/{report}', [VerificationController::class, 'updateStatus'])
        ->name('status.update');
});

/*
|--------------------------------------------------------------------------
| PBI-13–15: Penugasan, Progres & Bukti Tindak Lanjut
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin,Pengelola Fasilitas,Petugas Tindak Lanjut'])->group(function (): void {
    Route::get('follow-up-assignments/ready-reports', [FollowUpAssignmentController::class, 'readyReports'])
        ->name('follow-up-assignments.ready-reports')
        ->middleware('role:Admin,Pengelola Fasilitas');

    Route::get('follow-up-assignments/create', [FollowUpAssignmentController::class, 'create'])
        ->name('follow-up-assignments.create')
        ->middleware('role:Admin,Pengelola Fasilitas');

    Route::post('follow-up-assignments', [FollowUpAssignmentController::class, 'store'])
        ->name('follow-up-assignments.store')
        ->middleware('role:Admin,Pengelola Fasilitas');

    Route::resource('follow-up-assignments', FollowUpAssignmentController::class)
        ->only(['index', 'show'])
        ->parameter('follow-up-assignments', 'follow_up_assignment');

    Route::post('follow-up-assignments/{follow_up_assignment}/progress', [FollowUpProgressController::class, 'store'])
        ->name('follow-up-assignments.progress.store');

    Route::post('follow-up-assignments/{follow_up_assignment}/evidence', [FollowUpEvidenceController::class, 'store'])
        ->name('follow-up-assignments.evidence.store');

    Route::get('evidence/{evidence}', [FollowUpEvidenceController::class, 'show'])
        ->name('evidence.show');
});
