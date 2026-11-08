<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IssueCategoryController;

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
