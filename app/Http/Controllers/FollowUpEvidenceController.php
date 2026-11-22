<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFollowUpEvidenceRequest;
use App\Models\FollowUpAssignment;
use App\Models\FollowUpEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FollowUpEvidenceController extends Controller
{
    /**
     * Simpan bukti foto untuk entri progres tertentu.
     */
    public function store(StoreFollowUpEvidenceRequest $request, FollowUpAssignment $follow_up_assignment)
    {
        $validated = $request->validated();
        $progressId = (int) $validated['progress_id'];
        $description = $validated['description'] ?? null;
        $photos = $request->file('photos');
        $storedPaths = [];

        try {
            DB::transaction(function () use ($photos, $follow_up_assignment, $progressId, $description, &$storedPaths) {
                foreach ($photos as $photo) {
                    $extension = strtolower($photo->getClientOriginalExtension());
                    $systemName = Str::uuid() . '.' . $extension;
                    $directory = "evidence/{$follow_up_assignment->assignment_id}/{$progressId}";

                    $path = $photo->storeAs($directory, $systemName, 'local');
                    $storedPaths[] = $path;

                    FollowUpEvidence::create([
                        'progress_id'  => $progressId,
                        'file_path'    => $path,
                        'file_type'    => $photo->getMimeType(),
                        'uploaded_at'  => now(),
                        'description'  => $description,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            // Rollback: hapus file yang sudah tersimpan
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mengunggah bukti. Silakan coba lagi.');
        }

        return redirect()
            ->route('follow-up-assignments.show', $follow_up_assignment->assignment_id)
            ->with('success', 'Bukti berhasil diunggah (' . count($storedPaths) . ' foto).');
    }

    /**
     * Tampilkan/unduh file bukti (akses terbatas).
     */
    public function show(Request $request, FollowUpEvidence $evidence)
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $user->loadMissing('role');
        $roleName = $user->role?->role_name;

        // Muat relasi untuk cek kewenangan
        $evidence->loadMissing('progress.assignment');
        $assignment = $evidence->progress?->assignment;

        if (! $assignment) {
            abort(404);
        }

        // Admin & Pengelola Fasilitas boleh lihat semua
        if (! in_array($roleName, ['Admin', 'Pengelola Fasilitas'], true)) {
            // Petugas hanya boleh lihat miliknya
            if ($roleName === 'Petugas Tindak Lanjut') {
                if ((int) $assignment->assigned_to !== (int) $user->id) {
                    abort(403);
                }
            } else {
                abort(403);
            }
        }

        $disk = Storage::disk('local');
        if (! $disk->exists($evidence->file_path)) {
            abort(404, 'File bukti tidak ditemukan.');
        }

        return $disk->response($evidence->file_path);
    }
}
