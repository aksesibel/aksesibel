<?php

use App\Http\Controllers\FollowUpAssignmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| PBI-13: Penugasan Tindak Lanjut
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin,Petugas Tindak Lanjut'])->group(function () {
    Route::get('follow-up-assignments/ready-reports', [FollowUpAssignmentController::class, 'readyReports'])
        ->name('follow-up-assignments.ready-reports')
        ->middleware('role:Admin');

    Route::resource('follow-up-assignments', FollowUpAssignmentController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameter('follow-up-assignments', 'follow_up_assignment');
});
