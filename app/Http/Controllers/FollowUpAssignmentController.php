<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFollowUpAssignmentRequest;
use App\Models\FollowUpAssignment;
use App\Models\Report;
use App\Models\VerificationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FollowUpAssignmentController extends Controller
{
    /**
     * Daftar penugasan sesuai kewenangan pengguna.
     *
     * Admin: semua penugasan.
     * Petugas Tindak Lanjut: hanya penugasan miliknya.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $roleName = $user->role?->role_name;

        $query = FollowUpAssignment::with(['report', 'assignee', 'assigner']);

        if ($roleName === 'Petugas Tindak Lanjut') {
            $query->where('assigned_to', $user->id);
        }
        // Admin melihat semua

        $assignments = $query->orderByDesc('assigned_at')->paginate(15);

        return view('follow-up-assignments.index', compact('assignments'));
    }

    /**
     * Form buat penugasan baru.
     */
    public function create(Request $request)
    {
        $statusDalamTindakLanjut = VerificationStatus::where('status_name', 'Dalam Tindak Lanjut')->first();

        $reports = collect();
        if ($statusDalamTindakLanjut) {
            $reports = Report::where('status_id', $statusDalamTindakLanjut->status_id)
                ->with('facility', 'issueCategory')
                ->orderByDesc('report_id')
                ->get();
        }

        $officers = \App\Models\User::whereHas('role', function ($q) {
            $q->where('role_name', 'Petugas Tindak Lanjut');
        })->where('account_status', 'active')->get();

        return view('follow-up-assignments.create', compact('reports', 'officers'));
    }

    /**
     * Simpan penugasan baru.
     */
    public function store(StoreFollowUpAssignmentRequest $request)
    {
        $validated = $request->validated();

        $assignment = DB::transaction(function () use ($validated, $request) {
            return FollowUpAssignment::create([
                'report_id'         => $validated['report_id'],
                'assigned_to'       => $validated['assigned_to'],
                'assigned_by'       => $request->user()->id,
                'assignment_status' => 'assigned',
                'assigned_at'       => now(),
                'due_date'          => $validated['due_date'] ?? null,
                'assignment_notes'  => $validated['assignment_notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('follow-up-assignments.show', $assignment->assignment_id)
            ->with('success', 'Penugasan berhasil dibuat.');
    }

    /**
     * Detail penugasan.
     */
    public function show(Request $request, FollowUpAssignment $follow_up_assignment)
    {
        $user = $request->user();
        $roleName = $user->role?->role_name;

        // Petugas Tindak Lanjut hanya boleh lihat miliknya
        if ($roleName === 'Petugas Tindak Lanjut' && $follow_up_assignment->assigned_to !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke penugasan ini.');
        }

        $follow_up_assignment->load(['report.facility', 'report.issueCategory', 'report.status', 'assignee', 'assigner']);

        return view('follow-up-assignments.show', ['assignment' => $follow_up_assignment]);
    }
}

