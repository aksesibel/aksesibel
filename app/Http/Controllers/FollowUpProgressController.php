<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFollowUpProgressRequest;
use App\Models\FollowUpAssignment;
use App\Models\FollowUpProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class FollowUpProgressController extends Controller
{
    /**
     * Simpan catatan progres baru untuk penugasan tindak lanjut.
     */
    public function store(StoreFollowUpProgressRequest $request, FollowUpAssignment $follow_up_assignment): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $follow_up_assignment) {
            FollowUpProgress::create([
                'assignment_id'      => $follow_up_assignment->assignment_id,
                'actor_id'           => $request->user()->id,
                'progress_status'    => $validated['progress_status'],
                'action_description' => $validated['action_description'],
                'recorded_at'        => now(),
            ]);

            // Update assignment_status jika penugasan masih berstatus default 'assigned'
            $statusLower = strtolower(trim($validated['progress_status']));
            if (in_array($statusLower, ['selesai', 'completed', 'tuntas'])) {
                $follow_up_assignment->update(['assignment_status' => 'completed']);
            } elseif ($follow_up_assignment->assignment_status === 'assigned') {
                $follow_up_assignment->update(['assignment_status' => 'in_progress']);
            }
        });

        return redirect()
            ->route('follow-up-assignments.show', $follow_up_assignment->assignment_id)
            ->with('success', 'Catatan progres penanganan berhasil disimpan.');
    }
}

