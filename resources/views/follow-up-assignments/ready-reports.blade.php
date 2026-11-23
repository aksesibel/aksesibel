@extends('layouts.follow-up')

@section('title', 'Laporan Siap Ditugaskan')

@section('content')
<div class="space-y-6">
    <!-- Top Breadcrumb & Action Bar (Sesuai Gambar 1) -->
    <div class="flex items-center justify-between text-xs text-[#475569] border-b border-[#C3CFDF]/60 pb-3">
        <nav class="flex items-center space-x-2" aria-label="Breadcrumb">
            <span class="hover:text-[#0F172A]">Aksesibel</span>
            <span>/</span>
            <span class="font-semibold text-[#0F172A]">Antrean Penugasan</span>
        </nav>
        <div class="flex items-center space-x-3">
            <button type="button" aria-label="Notifikasi" class="p-1.5 rounded-md border border-gray-200 bg-white text-gray-500 hover:text-gray-700 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>
            <div class="inline-flex items-center px-2.5 py-1 rounded-md border border-blue-200 bg-blue-50/60 text-xs text-blue-900 font-medium">
                <svg class="w-3.5 h-3.5 mr-1 text-blue-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                <span>Admin Penugasan</span>
            </div>
        </div>
    </div>

    <!-- Page Header (Sesuai Gambar 1) -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#0F172A]">
            Verifikasi & Penugasan Laporan Masyarakat
        </h1>
        <p class="mt-1 text-sm text-[#475569]">
            Periksa kesesuaian fasilitas, uraian, dan bukti sebelum mendelegasikan penugasan ke petugas lapangan.
        </p>
    </div>

    <!-- 3 Stat Metrics Cards (Persis Gambar 1) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Kartu 1: Menunggu penugasan -->
        <div class="bg-white rounded-xl border border-[#C3CFDF] p-5 shadow-2xs relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-[#475569]">Menunggu penugasan</span>
                <span class="text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-4xl font-bold text-[#0F172A]">{{ $readyCount }}</div>
            <div class="mt-2 text-xs text-[#475569]">
                {{ $readyCount }} masuk • Siap diteruskan ke petugas
            </div>
        </div>

        <!-- Kartu 2: Penugasan berjalan -->
        <div class="bg-white rounded-xl border border-[#C3CFDF] p-5 shadow-2xs relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-[#475569]">Penugasan berjalan</span>
                <span class="text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-4xl font-bold text-[#0F172A]">{{ $inProgressCount }}</div>
            <div class="mt-2 text-xs text-[#475569]">
                Sedang ditangani oleh petugas lapangan
            </div>
        </div>

        <!-- Kartu 3: Selesai ditangani -->
        <div class="bg-white rounded-xl border border-[#C3CFDF] p-5 shadow-2xs relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-[#475569]">Tuntas ditangani</span>
                <span class="text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-4xl font-bold text-[#0F172A]">{{ $completedCount }}</div>
            <div class="mt-2 text-xs text-[#475569]">
                Penanganan fasilitas telah rampung
            </div>
        </div>
    </div>

    <!-- Dual Column Layout (Persis Gambar 1, 2, 3) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Daftar Kartu Laporan (lg:col-span-4) -->
        <div class="lg:col-span-4 space-y-3">
            <!-- Label & Input Cari -->
            <div>
                <label for="search" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                    Cari nomor atau fasilitas
                </label>
                <form method="GET" action="{{ route('follow-up-assignments.ready-reports') }}" class="relative">
                    @if(request('selected_id'))
                        <input type="hidden" name="selected_id" value="{{ request('selected_id') }}">
                    @endif
                    <input type="text"
                           name="search"
                           id="search"
                           value="{{ request('search') }}"
                           placeholder="Cari laporan"
                           class="w-full bg-white border border-[#C3CFDF] rounded-lg px-3.5 py-2 pr-9 text-sm text-[#0F172A] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] focus:border-[#1D4ED8]">
                    <button type="submit" aria-label="Cari" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Tabs: Masuk (X) & Riwayat (Persis Gambar 1) -->
            <div class="flex items-center space-x-2 pt-1 pb-1">
                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold border border-blue-600 text-blue-700 bg-white shadow-2xs">
                    Masuk ({{ $reports->total() }})
                </span>
                <a href="{{ route('follow-up-assignments.index') }}" class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium text-[#475569] hover:text-[#0F172A] hover:bg-gray-100">
                    Riwayat Penugasan
                </a>
            </div>

            <!-- List Cards (Persis Gambar 1 & 2) -->
            <div class="space-y-3">
                @forelse ($reports as $report)
                    @php
                        $isSelected = $selectedReport && $selectedReport->report_id === $report->report_id;
                    @endphp
                    <a href="{{ route('follow-up-assignments.ready-reports', array_merge(request()->query(), ['selected_id' => $report->report_id])) }}"
                       class="block rounded-xl p-4 bg-white transition-all {{ $isSelected ? 'border-2 border-blue-600 shadow-xs' : 'border border-[#C3CFDF] hover:border-gray-400' }}">
                        <!-- Top Line: ID & Status Pill -->
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-blue-700 tracking-tight">
                                {{ $report->report_number }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700">
                                <span class="w-1.5 h-1.5 mr-1.5 bg-blue-600 rounded-full"></span>
                                Laporan masuk
                            </span>
                        </div>

                        <!-- Title -->
                        <h2 class="mt-2 text-sm font-bold text-[#0F172A] leading-snug">
                            {{ $report->issueCategory?->category_name ?? 'Kendala Fasilitas' }}
                        </h2>

                        <!-- Facility Location -->
                        <p class="mt-1 text-xs text-[#475569]">
                            {{ $report->facility?->name ?? '-' }}
                        </p>

                        <!-- Date Time -->
                        <p class="mt-2 text-[11px] text-[#475569]">
                            {{ $report->created_at ? $report->created_at->format('d M • H.i') : '-' }}
                        </p>
                    </a>
                @empty
                    <div class="bg-white rounded-xl border border-[#C3CFDF] p-6 text-center text-xs text-[#475569]">
                        Tidak ada laporan masuk yang siap ditugaskan saat ini.
                    </div>
                @endforelse
            </div>

            <!-- Footer Pagination / Muat Berikutnya (Persis Gambar 2) -->
            <div class="pt-2 text-center space-y-2">
                <p class="text-xs text-[#475569]">
                    Menampilkan {{ $reports->count() }} dari {{ $reports->total() }} laporan
                </p>
                @if ($reports->hasPages())
                    <div>
                        {{ $reports->links() }}
                    </div>
                @else
                    <a href="{{ route('follow-up-assignments.index') }}" class="inline-block w-full text-center px-4 py-2 bg-white border border-[#C3CFDF] rounded-lg text-xs font-medium text-[#0F172A] hover:bg-gray-50">
                        Lihat Seluruh Riwayat Penugasan
                    </a>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Detail & Form Penugasan Laporan (lg:col-span-8, Persis Gambar 1, 2, 3) -->
        <div class="lg:col-span-8 space-y-4">
            @if ($selectedReport)
                <!-- Kartu Utama Detail Laporan -->
                <div class="bg-white rounded-xl border border-[#C3CFDF] p-6 shadow-2xs space-y-5">
                    <!-- Top Line: ID & Status Pill -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-700 tracking-tight">
                            {{ $selectedReport->report_number }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-blue-600 rounded-full"></span>
                            Laporan masuk
                        </span>
                    </div>

                    <!-- Title -->
                    <h2 class="text-xl font-bold text-[#0F172A]">
                        {{ $selectedReport->issueCategory?->category_name ?? 'Kendala Fasilitas Publik' }}
                    </h2>

                    <!-- Meta Box -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 border border-[#C3CFDF] rounded-md text-xs divide-y sm:divide-y-0 sm:divide-x divide-[#C3CFDF] overflow-hidden">
                        <div class="p-2.5 bg-white">
                            <span class="text-[#475569]">Pelapor: </span>
                            <span class="font-medium text-[#0F172A]">{{ $selectedReport->user?->name ?? 'Masyarakat' }} (privat)</span>
                        </div>
                        <div class="p-2.5 bg-white">
                            <span class="text-[#475569]">Masuk: </span>
                            <span class="font-medium text-[#0F172A]">
                                {{ $selectedReport->created_at ? $selectedReport->created_at->format('d M Y, H.i') : '-' }}
                            </span>
                        </div>
                    </div>

                    <!-- Subtitle Lokasi Fasilitas -->
                    <div>
                        <p class="text-sm font-bold text-[#0F172A]">
                            {{ $selectedReport->facility?->name ?? 'Fasilitas Publik' }}{{ $selectedReport->facility?->address ? ' • ' . $selectedReport->facility->address : '' }}
                        </p>
                        <!-- Description -->
                        <p class="mt-2 text-sm text-[#475569] leading-relaxed">
                            {{ $selectedReport->description }}
                        </p>
                    </div>

                    <!-- Bukti yang Dilampirkan -->
                    <div class="space-y-2 pt-2">
                        <h3 class="text-sm font-bold text-[#0F172A]">Bukti yang dilampirkan</h3>
                        @php
                            $reportEvidence = $selectedReport->evidences?->first();
                        @endphp
                        @if ($reportEvidence)
                            <div class="border border-[#C3CFDF] rounded-lg overflow-hidden bg-gray-100">
                                <div class="relative w-full h-48 bg-slate-200 flex items-center justify-center overflow-hidden">
                                    <svg class="w-14 h-14 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded">
                                        {{ $reportEvidence->caption ?? 'Dokumentasi Laporan Masyarakat' }}
                                    </span>
                                </div>
                                <div class="px-4 py-2.5 bg-white flex items-center justify-between text-xs text-[#475569] border-t border-[#C3CFDF]">
                                    <span class="truncate max-w-[320px]">
                                        {{ basename($reportEvidence->file_path) }}
                                        @if ($reportEvidence->file_type)
                                            &bull; {{ $reportEvidence->file_type }}
                                        @endif
                                        &bull; {{ $reportEvidence->uploaded_at ? $reportEvidence->uploaded_at->format('d M Y') : ($selectedReport->created_at ? $selectedReport->created_at->format('d M Y') : '-') }}
                                    </span>
                                    <span class="text-[11px] text-gray-500 italic">Bukti Pelapor</span>
                                </div>
                            </div>
                        @else
                            <div class="border border-[#C3CFDF] rounded-lg p-5 bg-gray-50 text-center text-xs text-[#475569]">
                                <svg class="mx-auto h-7 w-7 text-gray-400 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Tidak ada berkas bukti foto yang dilampirkan pada laporan ini.</span>
                            </div>
                        @endif
                    </div>

                    <!-- Informasi Verifikasi -->
                    <div class="space-y-3 pt-2">
                        <h3 class="text-sm font-bold text-[#0F172A]">Informasi Verifikasi Laporan</h3>
                        @php
                            $verification = $selectedReport->verifications?->sort(function ($a, $b) {
                                $aDated = $a->verified_at !== null;
                                $bDated = $b->verified_at !== null;

                                if ($aDated && ! $bDated) return -1;
                                if (! $aDated && $bDated) return 1;

                                if ($aDated && $bDated) {
                                    $cmp = $b->verified_at <=> $a->verified_at;
                                    if ($cmp !== 0) return $cmp;
                                }

                                return $b->verification_id <=> $a->verification_id;
                            })->first();

                            $verifiedAtDisplay = '-';
                            if ($verification?->verified_at) {
                                $verifiedAtDisplay = $verification->verified_at->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';
                            }

                            $rawResult = trim((string) ($verification?->verification_result ?? ''));
                            $hasResult = $rawResult !== '';
                            $resultLower = strtolower($rawResult);

                            if (! $hasResult) {
                                $resultText = 'Hasil verifikasi belum dicatat';
                                $badgeClass = 'bg-gray-100 text-gray-700 border border-gray-200';
                            } elseif (in_array($resultLower, ['valid', 'terverifikasi', 'sesuai', 'disetujui'], true)) {
                                $resultText = $rawResult;
                                $badgeClass = 'bg-green-100 text-green-800 border border-green-200';
                            } elseif (in_array($resultLower, ['tidak valid', 'ditolak', 'tidak sesuai'], true)) {
                                $resultText = $rawResult;
                                $badgeClass = 'bg-red-100 text-red-800 border border-red-200';
                            } else {
                                $resultText = $rawResult;
                                $badgeClass = 'bg-gray-100 text-gray-700 border border-gray-200';
                            }
                        @endphp
                        @if ($verification)
                            <div class="p-3.5 rounded-lg bg-blue-50/50 border border-blue-100 space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-blue-900">
                                        Diverifikasi oleh: {{ $verification->verifier?->name ?? 'Verifikator' }}
                                    </span>
                                    <span class="text-blue-700">
                                        {{ $verifiedAtDisplay }}
                                    </span>
                                </div>
                                <div class="text-[#0F172A]">
                                    <span class="text-[#475569]">Hasil Verifikasi:</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $badgeClass }} ml-1">
                                        {{ $resultText }}
                                    </span>
                                </div>
                                @if ($verification->notes)
                                    <p class="text-gray-700 italic bg-white p-2.5 rounded border border-blue-100/60">
                                        "{{ $verification->notes }}"
                                    </p>
                                @endif
                            </div>
                        @else
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200 text-xs text-[#475569]">
                                Laporan telah berstatus <strong>Dalam Tindak Lanjut</strong> dan siap untuk ditugaskan ke petugas lapangan.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Kartu Keputusan Penugasan (Persis Gambar 2 & 3) -->
                <div class="bg-white rounded-xl border border-[#C3CFDF] p-6 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#0F172A]">Keputusan penugasan tindak lanjut</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                            Siap ditugaskan
                        </span>
                    </div>

                    <!-- Callout Amber (Persis Gambar 3) -->
                    <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start space-x-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="space-y-0.5">
                            <p class="font-bold">Pelapor & publik akan menerima status pembaruan penugasan</p>
                            <p class="text-amber-800">
                                Laporan yang ditugaskan akan langsung masuk ke antrean tugas Petugas Tindak Lanjut terkait untuk ditangani di lapangan.
                            </p>
                        </div>
                    </div>

                    <!-- Tombol Aksi (Persis Gambar 3) -->
                    <div class="pt-2 flex items-center justify-between">
                        <a href="{{ route('follow-up-assignments.index') }}" class="px-4 py-2 border border-[#C3CFDF] text-xs font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Batal
                        </a>
                        <a href="{{ route('follow-up-assignments.create', ['report_id' => $selectedReport->report_id]) }}"
                           class="inline-flex items-center px-5 py-2.5 border border-transparent text-xs font-bold rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-2xs">
                            + Tugaskan Petugas
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl border border-[#C3CFDF] p-12 text-center text-[#475569]">
                    <p class="text-sm font-semibold text-[#0F172A]">Pilih salah satu laporan dari daftar di sebelah kiri.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
