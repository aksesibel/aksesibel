<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\VerificationStatus;
use Illuminate\Http\Request;

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
}
