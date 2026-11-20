<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\VerificationStatus;
use Illuminate\Http\Request;
use App\Models\VerificationHistory;

class VerificationController extends Controller
{
    public function antrean(Request $request)
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
            'evidences',
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

    public function riwayat(Request $request)
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

        if (!$detail) {
            $detail = $riwayat->first();
        }

        return view('verifikasi.riwayat', compact(
            'riwayat',
            'statistik',
            'detail'
        ));
    }
}
