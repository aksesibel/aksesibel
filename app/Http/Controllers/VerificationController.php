<?php

namespace App\Http\Controllers;

use App\Models\Report;
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

        $laporan = $query->latest()->paginate(10)->withQueryString();

        $statistik = [
            'menunggu' => Report::where('status_id', $menungguId)->count(),
            'terverifikasi' => Report::whereHas('status', function ($q) {
                $q->where('status_name', 'Terverifikasi');
            })->count(),
            'ditolak' => Report::whereHas('status', function ($q) {
                $q->where('status_name', 'Ditolak');
            })->count(),
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
            'laporanSebelumnya'
        ));
    }

    public function riwayat(Request $request): View
    {
        // Mengambil seluruh riwayat verifikasi
        $query = VerificationHistory::with([
            'verification.report.facility',
            'verification.verifier',
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

        return view('verifikasi.riwayat', compact(
            'riwayat',
            'statistik',
            'detail'
        ));
    }

    public function kelolaStatus(Request $request): View
    {
        $query = Report::with([
            'user',
            'facility',
            'issueCategory',
            'status',
        ]);

        // Pencarian laporan
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('facility', function ($facility) use ($search) {
                        $facility->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status_id', $request->status);
        }

        // Filter tanggal laporan
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
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

        // Daftar laporan
        $laporan = $query->latest('created_at')
            ->paginate(5)
            ->withQueryString();

        // Detail laporan yang dipilih
        $detail = null;

        if ($request->filled('kelola')) {
            $detail = Report::with([
                'user',
                'facility',
                'issueCategory',
                'status',
                'evidences',
            ])->find($request->kelola);
        }

        if (! $detail) {
            $detail = $laporan->first()?->load('evidences');
        }

        // Daftar status untuk pilihan form
        $daftarStatus = VerificationStatus::orderBy('status_id')->get();

        return view('verifikasi.status', compact(
            'laporan',
            'statistik',
            'detail',
            'daftarStatus'
        ));
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

        return redirect()->route('verifikasi.status', ['kelola' => $report->getKey()])
            ->with('success', $changed ? 'Status laporan berhasil diperbarui.' : 'Status laporan sudah sesuai.');
    }
}
