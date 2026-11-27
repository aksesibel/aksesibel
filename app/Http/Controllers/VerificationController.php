<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Verification;
use App\Models\VerificationHistory;
use App\Models\VerificationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function antrean(Request $request): View
    {
        $menungguId = VerificationStatus::where(
            'status_name',
            'Menunggu Verifikasi'
        )->value('status_id');

        $query = Report::with([
            'user',
            'facility',
            'issueCategory',
            'status',
        ]);

        // Hanya menampilkan laporan yang menunggu verifikasi
        if ($menungguId) {
            $query->where('status_id', $menungguId);
        } else {
            $query->whereRaw('1 = 0');
        }

        // Pencarian laporan
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                    ->orWhereHas('facility', function ($facility) use ($search) {
                        $facility->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($user) use ($search) {
                        $user->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $laporan = $query->latest()->orderByDesc('report_id')->paginate(3)->withQueryString();
        $selectedId = filter_var($request->query('laporan'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $selected = $laporan->getCollection()->firstWhere('report_id', $selectedId) ?? $laporan->first();
        $selected?->load(['evidences', 'facilityAccessibility.accessibilityType']);
        $decisions = VerificationStatus::whereIn('status_name', ['Terverifikasi', 'Ditolak'])
            ->pluck('status_id', 'status_name');

        $statistik = [
            'menunggu' => Report::where('status_id', $menungguId)->count(),
            'terverifikasi' => Verification::where('verification_result', 'Terverifikasi')
                ->whereDate('verified_at', today())
                ->distinct()->count('report_id'),
            'ditolak' => Verification::where('verification_result', 'Ditolak')
                ->whereBetween('verified_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->distinct()->count('report_id'),
        ];

        $laporanHariIni = Report::where('status_id', $menungguId)
            ->whereDate('created_at', today())
            ->count();

        $laporanSebelumnya = Report::where('status_id', $menungguId)
            ->whereDate('created_at', '<', today())
            ->count();

        return view('verifikasi.antrean', compact(
            'laporan',
            'statistik',
            'laporanHariIni',
            'laporanSebelumnya',
            'selected',
            'decisions',
        ));
    }

    public function riwayat(Request $request): View
    {
        // Mengambil seluruh riwayat verifikasi
        $query = VerificationHistory::with([
            'verification.report.facility',
            'verification.verifier',
            'actor',
            'newStatus',
        ])->whereHas('newStatus', function ($q) {
            $q->whereIn('status_name', [
                'Terverifikasi',
                'Ditolak',
            ]);
        });

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('verification.report', function ($report) use ($search) {
                    $report->where('report_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('facility', function ($facility) use ($search) {
                            $facility->where('name', 'like', "%{$search}%");
                        });
                })->orWhereHas('verification.verifier', function ($verifier) use ($search) {
                    $verifier->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter hasil verifikasi
        if ($request->filled('hasil')) {
            $query->whereHas('newStatus', function ($q) use ($request) {
                $q->where('status_name', $request->hasil);
            });
        }

        // Filter tanggal pemeriksaan
        if ($request->filled('tanggal')) {
            $query->whereDate('changed_at', $request->tanggal);
        }

        // Statistik
        $statistik = [
            'total' => VerificationHistory::whereHas('newStatus', function ($q) {
                $q->whereIn('status_name', ['Terverifikasi', 'Ditolak']);
            })->count(),

            'diterima' => VerificationHistory::whereHas('newStatus', function ($q) {
                $q->where('status_name', 'Terverifikasi');
            })->count(),

            'ditolak' => VerificationHistory::whereHas('newStatus', function ($q) {
                $q->where('status_name', 'Ditolak');
            })->count(),

            'hari_ini' => VerificationHistory::whereHas('newStatus', function ($q) {
                $q->whereIn('status_name', ['Terverifikasi', 'Ditolak']);
            })->whereDate('changed_at', today())->count(),
        ];

        // Pagination
        $riwayat = $query->latest('changed_at')
            ->orderByDesc('history_id')
            ->paginate(5)
            ->withQueryString();

        // Detail riwayat yang dipilih
        $detail = null;

        if ($request->filled('detail')) {
            $detail = (clone $query)
                ->where('history_id', $request->detail)
                ->first();
        }

        if (! $detail) {
            $detail = $riwayat->first();
        }

        $detail?->loadMissing('verification.report.evidences');

        return view('verifikasi.riwayat', compact(
            'riwayat',
            'statistik',
            'detail'
        ));
    }

    public function kelolaStatus(Request $request): View
    {
        $filters = $this->statusListFilters($request);
        $relations = ['facility', 'issueCategory', 'status'];
        $query = Report::with($relations);

        if (isset($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                    ->orWhere('report_title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('facility', function ($facility) use ($search) {
                        $facility->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['status'])) {
            $query->where('status_id', $filters['status']);
        }

        if (isset($filters['tanggal'])) {
            $query->whereDate('created_at', $filters['tanggal']);
        }

        // Statistik status laporan
        $statistik = [
            'menunggu' => Report::whereHas('status', function ($q) {
                $q->where('status_name', 'Menunggu Verifikasi');
            })->count(),

            'terverifikasi' => Report::whereHas('status', function ($q) {
                $q->where('status_name', 'Terverifikasi');
            })->count(),

            'ditolak' => Report::whereHas('status', function ($q) {
                $q->where('status_name', 'Ditolak');
            })->count(),

            'lanjutan' => Report::whereHas('status', function ($q) {
                $q->where('status_name', 'Dalam Tindak Lanjut');
            })->count(),
        ];

        $query->latest('created_at')->orderByDesc('report_id');
        $laporan = $query->paginate(5);
        if ($laporan->currentPage() > $laporan->lastPage()) {
            $laporan = $query->paginate(5, page: $laporan->lastPage(), total: $laporan->total());
        }
        $laporan->appends($filters)->fragment('daftar-status');

        $listQuery = $filters;
        if ($laporan->currentPage() > 1) {
            $listQuery['page'] = $laporan->currentPage();
        }

        $detailId = filter_var($request->query('kelola'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $detail = $detailId
            ? ($laporan->getCollection()->firstWhere('report_id', $detailId) ?? Report::with($relations)->find($detailId))
            : null;
        $detail ??= $laporan->first();
        $detail?->loadMissing('evidences');

        $daftarStatus = VerificationStatus::orderBy('status_id')->get();

        return view('verifikasi.status', compact('laporan', 'statistik', 'detail', 'daftarStatus', 'filters', 'listQuery'));
    }

    /**
     * @return array{search?: string, status?: int, tanggal?: string}
     */
    private function statusListFilters(Request $request): array
    {
        $search = $request->query('search');
        $date = $request->query('tanggal');
        $status = filter_var($request->query('status'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return array_filter([
            'search' => is_string($search) ? trim($search) : null,
            'status' => $status ?: null,
            'tanggal' => is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) ? $date : null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function updateStatus(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'status_id' => ['required', 'integer', Rule::exists(VerificationStatus::class, 'status_id')],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $status = VerificationStatus::findOrFail($validated['status_id']);
        $actor = $request->user();
        $notes = $validated['notes'] ?? null;

        $changed = DB::transaction(function () use ($report, $status, $actor, $notes): bool {
            $currentReport = Report::lockForUpdate()->findOrFail($report->getKey());
            $previousStatus = $currentReport->status_id;

            if ((int) $previousStatus === $status->getKey()) {
                return false;
            }

            $changedAt = now();

            $verification = $currentReport->verifications()->create([
                'verifier_id' => $actor->getKey(),
                'verification_result' => $status->status_name,
                'notes' => $notes,
                'verified_at' => $changedAt,
            ]);

            $verification->histories()->create([
                'actor_id' => $actor->getKey(),
                'previous_status_id' => $previousStatus,
                'new_status_id' => $status->getKey(),
                'notes' => $notes,
                'changed_at' => $changedAt,
            ]);

            $currentReport->status()->associate($status)->save();

            return true;
        }, attempts: 3);

        $listQuery = $this->statusListFilters($request);
        $page = filter_var($request->query('page'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 2]]);
        if ($page) {
            $listQuery['page'] = $page;
        }

        return redirect()->route('verifikasi.status', [...$listQuery, 'kelola' => $report->getKey()])
            ->with('success', $changed ? 'Status laporan berhasil diperbarui.' : 'Status laporan sudah sesuai.');
    }
}
