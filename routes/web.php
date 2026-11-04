<?php

use App\Http\Controllers\FollowUpAssignmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return '
    <div style="font-family: sans-serif; padding: 40px; text-align: center;">
        <h2 style="margin-bottom: 8px;">Aksesibel - Autentikasi Pengujian Lokal</h2>
        <p style="color: #666; margin-bottom: 24px;">Pilih akun untuk masuk dan menguji modul Penugasan & Progres:</p>
        <div style="display: inline-flex; gap: 12px;">
            <a href="/login-as/admin" style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500;">Login sebagai Admin</a>
            <a href="/login-as/petugas" style="background: #059669; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500;">Login sebagai Petugas Tindak Lanjut</a>
        </div>
    </div>';
})->name('login');

// Helper route untuk login pengujian lokal
Route::get('/login-as/{role}', function (string $role) {
    $targetRole = $role === 'petugas' ? 'Petugas Tindak Lanjut' : 'Admin';
    $user = \App\Models\User::whereHas('role', function ($q) use ($targetRole) {
        $q->where('role_name', $targetRole);
    })->first();

    if (! $user) {
        return "Pengguna dengan role {$targetRole} tidak ditemukan di database. Pastikan seeder sudah dijalankan: php artisan db:seed --class=MasterDataSeeder";
    }

    \Illuminate\Support\Facades\Auth::login($user);

    if ($targetRole === 'Admin') {
        return redirect()->route('follow-up-assignments.ready-reports');
    }

    return redirect()->route('follow-up-assignments.index');
});

/*
|--------------------------------------------------------------------------
| PBI-13 & PBI-14: Penugasan & Progres Tindak Lanjut
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin,Petugas Tindak Lanjut'])->group(function () {
    Route::get('follow-up-assignments/ready-reports', [FollowUpAssignmentController::class, 'readyReports'])
        ->name('follow-up-assignments.ready-reports')
        ->middleware('role:Admin');

    Route::get('follow-up-assignments/create', [FollowUpAssignmentController::class, 'create'])
        ->name('follow-up-assignments.create')
        ->middleware('role:Admin');

    Route::post('follow-up-assignments', [FollowUpAssignmentController::class, 'store'])
        ->name('follow-up-assignments.store')
        ->middleware('role:Admin');

    Route::resource('follow-up-assignments', FollowUpAssignmentController::class)
        ->only(['index', 'show'])
        ->parameter('follow-up-assignments', 'follow_up_assignment');

    Route::post('follow-up-assignments/{follow_up_assignment}/progress', [\App\Http\Controllers\FollowUpProgressController::class, 'store'])
        ->name('follow-up-assignments.progress.store');
});
