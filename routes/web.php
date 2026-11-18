<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IssueCategoryController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});

// PBI-08: Kelola Kategori Kendala
Route::resource(
    'issue-categories',
    IssueCategoryController::class
)->except(['show', 'destroy']);

Route::patch(
    '/issue-categories/{issueCategory}/toggle-status',
    [IssueCategoryController::class, 'toggleStatus']
)->name('issue-categories.toggle-status');

// PBI-07 dan PBI-09: Kelola Laporan Masyarakat
Route::get('/reports', [ReportController::class, 'index'])
    ->name('reports.index');

Route::get('/reports/create', [ReportController::class, 'create'])
    ->name('reports.create');

Route::post('/reports', [ReportController::class, 'store'])
    ->name('reports.store');

Route::get('/reports/{report}', [ReportController::class, 'show'])
    ->name('reports.show');
