<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFollowUpAssignmentRequest;
use App\Models\FollowUpAssignment;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FollowUpAssignmentController extends Controller
{
    /**
     * Daftar penugasan sesuai kewenangan pengguna.
     *
     * Admin: semua penugasan.
     * Petugas Tindak Lanjut: hanya penugasan miliknya.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user && $user->account_status !== 'active') {
            abort(403, 'Akun Anda tidak aktif atau dinonaktifkan.');
        }

        $roleName = $user->role?->role_name;

        $query = FollowUpAssignment::with(['report.facility', 'assignee', 'assigner']);

        if ($roleName === 'Petugas Tindak Lanjut') {
            $query->where('assigned_to', $user->id);
        }
        $summary = (clone $query)->selectRaw('assignment_status, COUNT(*) AS total')
            ->groupBy('assignment_status')->pluck('total', 'assignment_status');
        $selectedStatus = $request->validate([
            'status' => ['nullable', 'in:assigned,in_progress,completed,cancelled'],
        ])['status'] ?? null;

        $assignments = $query->when($selectedStatus, fn ($items) => $items->where('assignment_status', $selectedStatus))
            ->orderByDesc('assigned_at')->paginate(15)->withQueryString();

        return view('follow-up-assignments.index', compact('assignments', 'summary', 'selectedStatus'));
    }

    public function readyReports(Request $request): View
    {
        abort_unless($request->user()?->account_status === 'active', 403, 'Akun Anda tidak aktif atau dinonaktifkan.');

        $filters = $request->validate([
            'stage' => ['nullable', 'in:all,unassigned,review'],
            'search' => ['nullable', 'string', 'max:255'],
            'selected_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $stage = $filters['stage'] ?? 'all';
        $search = trim($filters['search'] ?? '');
        $openStatuses = ['assigned', 'in_progress'];

        $queue = Report::whereRelation('status', 'status_name', 'Dalam Tindak Lanjut');
        $unassigned = (clone $queue)->whereDoesntHave(
            'followUpAssignments',
            fn (Builder $assignments) => $assignments->whereIn('assignment_status', [...$openStatuses, 'completed']),
        );
        $handling = (clone $queue)->whereHas(
            'followUpAssignments',
            fn (Builder $assignments) => $assignments->whereIn('assignment_status', $openStatuses),
        );
        $review = (clone $queue)
            ->whereRelation('followUpAssignments', 'assignment_status', 'completed')
            ->whereDoesntHave('followUpAssignments', fn (Builder $assignments) => $assignments->whereIn('assignment_status', $openStatuses));

        $reportQuery = match ($stage) {
            'unassigned' => clone $unassigned,
            'review' => clone $review,
            default => clone $queue,
        };
        $relations = [
            'facility',
            'user',
            'followUpAssignments' => fn ($assignments) => $assignments
                ->whereIn('assignment_status', [...$openStatuses, 'completed'])
                ->with('assignee')->latest('assigned_at')->orderByDesc('assignment_id'),
        ];

        $reports = $reportQuery->with($relations)
            ->when($search !== '', fn (Builder $reports) => $reports->where(function (Builder $matching) use ($search): void {
                $matching->where('report_number', 'like', "%{$search}%")
                    ->orWhere('report_title', 'like', "%{$search}%")
                    ->orWhereHas('facility', fn (Builder $facilities) => $facilities->where('name', 'like', "%{$search}%"));
            }))
            ->orderByDesc('report_id')->paginate(10)->withQueryString();

        $selected = $reports->getCollection()->firstWhere('report_id', $filters['selected_id'] ?? null) ?? $reports->first();
        $selectedAssignments = $selected?->followUpAssignments ?? collect();
        $selectedActive = $selectedAssignments->first(fn (FollowUpAssignment $assignment): bool => in_array($assignment->assignment_status, $openStatuses, true));

        $assignmentReport = $selected && $selectedAssignments->isEmpty()
            ? $selected
            : ($search === '' && $stage === 'all' ? (clone $unassigned)->with($relations)->latest('report_id')->first() : null);
        $reviewReport = $selected && ! $selectedActive && $selectedAssignments->isNotEmpty()
            ? $selected
            : ($search === '' && $stage === 'all' ? (clone $review)->with($relations)->latest('report_id')->first() : null);

        $assignmentReport?->load([
            'evidences',
            'verifications' => fn ($verifications) => $verifications->with('verifier')
                ->orderByRaw('CASE WHEN verified_at IS NOT NULL THEN 0 ELSE 1 END')
                ->orderByDesc('verified_at')->orderByDesc('verification_id'),
        ]);
        $reviewAssignment = $reviewReport?->followUpAssignments->first();
        $reviewAssignment?->loadMissing('progress.evidences');

        return view('follow-up-assignments.ready-reports', [
            'reports' => $reports,
            'selected' => $selected,
            'stage' => $stage,
            'search' => $search,
            'counts' => [
                'unassigned' => $unassigned->count(),
                'handling' => $handling->count(),
                'review' => $review->count(),
            ],
            'assignmentReport' => $assignmentReport,
            'officers' => $assignmentReport ? $this->activeOfficers() : collect(),
            'reviewReport' => $reviewReport,
            'reviewAssignment' => $reviewAssignment,
            'completedStatus' => $reviewAssignment && $request->user()->role?->role_name === Role::ADMIN
                ? VerificationStatus::where('status_name', 'Selesai')->value('status_id')
                : null,
        ]);
    }

    private function activeOfficers(): Collection
    {
        return User::whereRelation('role', 'role_name', Role::FOLLOW_UP_OFFICER)
            ->where('account_status', 'active')->orderBy('name')->get();
    }

    /**
     * Form buat penugasan baru.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        if ($user && $user->account_status !== 'active') {
            abort(403, 'Akun Anda tidak aktif atau dinonaktifkan.');
        }

        $statusDalamTindakLanjut = VerificationStatus::where('status_name', 'Dalam Tindak Lanjut')->first();

        $reports = collect();
        if ($statusDalamTindakLanjut) {
            $reports = Report::where('status_id', $statusDalamTindakLanjut->status_id)
                ->with(['facility', 'issueCategory', 'status'])
                ->orderByDesc('report_id')
                ->get();
        }

        $selectedReportId = $request->query('report_id');
        $selectedReport = null;
        if ($selectedReportId) {
            $selectedReport = $reports->firstWhere('report_id', (int) $selectedReportId);
        }

        $officers = $this->activeOfficers();

        return view('follow-up-assignments.create', compact('reports', 'officers', 'selectedReport'));
    }

    /**
     * Simpan penugasan baru.
     */
    public function store(StoreFollowUpAssignmentRequest $request)
    {
        $validated = $request->validated();

        $assignment = DB::transaction(function () use ($validated, $request) {
            return FollowUpAssignment::create([
                'report_id' => $validated['report_id'],
                'assigned_to' => $validated['assigned_to'],
                'assigned_by' => $request->user()->id,
                'assignment_status' => 'assigned',
                'assigned_at' => now(),
                'due_date' => $validated['due_date'] ?? null,
                'assignment_notes' => $validated['assignment_notes'] ?? null,
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
        if ($user && $user->account_status !== 'active') {
            abort(403, 'Akun Anda tidak aktif atau dinonaktifkan.');
        }

        $roleName = $user->role?->role_name;

        // Petugas Tindak Lanjut hanya boleh lihat miliknya
        if ($roleName === 'Petugas Tindak Lanjut' && (int) $follow_up_assignment->assigned_to !== (int) $user->id) {
            abort(403, 'Anda tidak memiliki akses ke penugasan ini.');
        }

        // Masyarakat atau role lain yang tidak berwenang dilarang mengakses
        if (! in_array($roleName, ['Admin', 'Pengelola Fasilitas', 'Petugas Tindak Lanjut'], true)) {
            abort(403, 'Anda tidak memiliki akses ke penugasan ini.');
        }

        $follow_up_assignment->load([
            'report.facility',
            'report.issueCategory',
            'report.status',
            'report.verifications.verifier',
            'assignee',
            'assigner',
            'progress' => function ($q) {
                $q->with(['actor', 'evidences'])->orderByDesc('recorded_at');
            },
        ]);

        return view('follow-up-assignments.show', ['assignment' => $follow_up_assignment]);
    }
}
