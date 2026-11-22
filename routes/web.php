<?php

use App\Http\Controllers\FollowUpAssignmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Autentikasi Pengujian & Akses Demo
|--------------------------------------------------------------------------
| Route helper di bawah ini dibatasi secara ketat hanya untuk lingkungan
| lokal atau pengujian (testing) dan tidak tersedia pada lingkungan bersama.
*/
Route::get('/login', function () {
    if (app()->environment('local')) {
        return '
        <div style="font-family: sans-serif; padding: 40px; text-align: center;">
            <h2 style="margin-bottom: 8px;">Aksesibel - Autentikasi Pengujian Lokal</h2>
            <p style="color: #666; margin-bottom: 24px;">Pilih akun aktif untuk masuk dan menguji modul Penugasan & Progres:</p>
            <div style="display: inline-flex; gap: 12px;">
                <a href="/login-as/admin" style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500;">Login sebagai Admin</a>
                <a href="/login-as/petugas" style="background: #059669; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500;">Login sebagai Petugas Tindak Lanjut</a>
            </div>
        </div>';
    }

    if (app()->environment('testing')) {
        return response('login', 200);
    }

    abort(404);
})->name('login');

Route::get('/login-as/{role}', function (string $role) {
    if (! app()->environment(['local', 'testing'])) {
        abort(404);
    }

    $allowedRoles = [
        'admin'     => 'Admin',
        'pengelola' => 'Pengelola Fasilitas',
        'petugas'   => 'Petugas Tindak Lanjut',
    ];

    if (! array_key_exists($role, $allowedRoles)) {
        abort(404, 'Role demo tidak valid.');
    }

    $targetRole = $allowedRoles[$role];
    $user = \App\Models\User::whereHas('role', function ($q) use ($targetRole) {
        $q->where('role_name', $targetRole);
    })->where('account_status', 'active')->first();

    if (! $user) {
        abort(403, "Pengguna aktif dengan role {$targetRole} tidak ditemukan.");
    }

    \Illuminate\Support\Facades\Auth::login($user);

    if (in_array($targetRole, ['Admin', 'Pengelola Fasilitas'], true)) {
        return redirect()->route('follow-up-assignments.ready-reports');
    }

    return redirect()->route('follow-up-assignments.index');
})->name('demo.login-as');

/*
|--------------------------------------------------------------------------
| PBI-13 & PBI-14: Penugasan & Progres Tindak Lanjut
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin,Pengelola Fasilitas,Petugas Tindak Lanjut'])->group(function () {
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

    Route::post('follow-up-assignments/{follow_up_assignment}/progress', [\App\Http\Controllers\FollowUpProgressController::class, 'store'])
        ->name('follow-up-assignments.progress.store');

    Route::post('follow-up-assignments/{follow_up_assignment}/evidence', [\App\Http\Controllers\FollowUpEvidenceController::class, 'store'])
        ->name('follow-up-assignments.evidence.store');

    Route::get('evidence/{evidence}', [\App\Http\Controllers\FollowUpEvidenceController::class, 'show'])
        ->name('evidence.show');
});
