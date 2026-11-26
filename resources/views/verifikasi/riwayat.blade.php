@extends('layouts.app')

@section('title', 'Riwayat Verifikasi')
@section('breadcrumb', 'Riwayat verifikasi')

@section('content')
<div class="page-heading">
    <div>
        <h1>Riwayat Verifikasi Laporan</h1>
        <p>Telusuri keputusan dan catatan pemeriksaan laporan masyarakat.</p>
    </div>
</div>

<div class="grid grid-cols-2 gap-3 xl:grid-cols-4 xl:gap-4">
    <x-workspace-metric label="Total diverifikasi" :value="$statistik['total']" description="Seluruh laporan yang telah diperiksa" icon="report" />
    <x-workspace-metric label="Diterima" :value="$statistik['diterima']" description="Laporan memenuhi kriteria" icon="shield" />
    <x-workspace-metric label="Ditolak" :value="$statistik['ditolak']" description="Laporan belum memenuhi kriteria" icon="close" />
    <x-workspace-metric label="Verifikasi hari ini" :value="$statistik['hari_ini']" :description="today()->translatedFormat('j F Y')" icon="shield" />
</div>

<section class="panel overflow-hidden !p-0" aria-labelledby="history-list">
    <div class="grid gap-4 p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="history-list">Daftar riwayat verifikasi</h2>
            <span class="badge bg-blue-50 text-primary">{{ $riwayat->total() }} laporan</span>
        </div>
        <form action="{{ route('verifikasi.riwayat') }}" method="GET" class="grid items-end gap-3 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)_minmax(0,1.2fr)_auto]">
            <div>
                <label for="search">Cari riwayat</label>
                <div class="relative">
                    <x-icon name="search" :size="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-slate-500" />
                    <input class="field !pl-9" id="search" name="search" type="search" value="{{ is_string(request('search')) ? request('search') : '' }}" placeholder="Nomor laporan atau nama petugas">
                </div>
            </div>
            <div>
                <label for="hasil">Hasil verifikasi</label>
                <select class="field" id="hasil" name="hasil">
                    <option value="">Semua hasil</option>
                    <option value="Terverifikasi" @selected(request('hasil') === 'Terverifikasi')>Diterima</option>
                    <option value="Ditolak" @selected(request('hasil') === 'Ditolak')>Ditolak</option>
                </select>
            </div>
            <div>
                <label for="tanggal">Tanggal pemeriksaan</label>
                <input class="field" id="tanggal" name="tanggal" type="date" value="{{ is_string(request('tanggal')) ? request('tanggal') : '' }}">
            </div>
            <div class="flex gap-2">
                <button class="button-secondary" type="submit">Filter</button>
                @if(request()->anyFilled(['search', 'hasil', 'tanggal']))
                    <a href="{{ route('verifikasi.riwayat') }}" class="button-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="relative max-w-full overflow-x-auto">
        <table class="min-w-[860px]" aria-label="Tabel riwayat verifikasi">
            <thead>
                <tr>
                    <th scope="col">Nomor laporan</th><th scope="col">Uraian laporan</th>
                    <th scope="col">Nama verifikator</th><th scope="col">Waktu pemeriksaan</th>
                    <th scope="col">Hasil verifikasi</th><th scope="col">Catatan</th><th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $history)
                    @php
                        $report = $history->verification?->report;
                        $accepted = $history->newStatus?->status_name === 'Terverifikasi';
                        $isSelected = $detail?->is($history);
                    @endphp
                    <tr @class(['!bg-blue-50' => $isSelected])>
                        <td class="whitespace-nowrap font-semibold text-primary">{{ $report?->report_number ?? '—' }}</td>
                        <td class="min-w-40 font-semibold">{{ $report?->report_title ?: ($report?->description ?? '—') }}</td>
                        <td class="min-w-28 text-sm">{{ $history->actor?->name ?? $history->verification?->verifier?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap text-xs text-slate-600">
                            @if($history->changed_at)
                                <time datetime="{{ $history->changed_at->toIso8601String() }}">{{ $history->changed_at->translatedFormat('d M Y') }}<br>{{ $history->changed_at->format('H.i') }} WIB</time>
                            @else
                                —
                            @endif
                        </td>
                        <td><span @class(['badge whitespace-nowrap', 'bg-emerald-50 text-emerald-800' => $accepted, 'bg-red-50 text-red-800' => ! $accepted])>{{ $accepted ? 'Diterima' : 'Ditolak' }}</span></td>
                        <td class="min-w-40 text-xs leading-5 text-slate-600">{{ $history->notes ?: '—' }}</td>
                        <td>
                            <a class="button-secondary !min-h-9 !px-3 !py-1.5" href="{{ route('verifikasi.riwayat', [...request()->except('detail'), 'detail' => $history->getKey()]) }}#detail-verifikasi" @if($isSelected) aria-current="true" @endif>Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="text-sm text-slate-600" colspan="7">Belum ada riwayat verifikasi untuk filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 sm:px-5">
        <p class="text-xs text-slate-600">Menampilkan {{ $riwayat->firstItem() ?? 0 }}–{{ $riwayat->lastItem() ?? 0 }} dari {{ $riwayat->total() }} laporan</p>
        {{ $riwayat->links() }}
    </div>
</section>

@if($detail)
    @php
        $detailReport = $detail->verification?->report;
        $detailAccepted = $detail->newStatus?->status_name === 'Terverifikasi';
    @endphp
    <section class="panel grid scroll-mt-5 gap-4" id="detail-verifikasi" aria-labelledby="history-detail">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="history-detail">Detail verifikasi laporan</h2>
            <span @class(['badge', 'bg-emerald-50 text-emerald-800' => $detailAccepted, 'bg-red-50 text-red-800' => ! $detailAccepted])>{{ $detailAccepted ? 'Diterima' : 'Ditolak' }}</span>
        </div>
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)]">
            <div class="grid min-w-0 gap-3">
                <p class="text-sm font-semibold text-primary">{{ $detailReport?->report_number ?? '—' }}</p>
                <h3 class="text-lg font-bold">{{ $detailReport?->report_title ?: ($detailReport?->description ?? '—') }}</h3>
                <div class="flex flex-col items-start gap-4 sm:flex-row">
                    @if($detailReport?->evidences->isNotEmpty())
                        <div class="grid w-full shrink-0 gap-2 sm:w-48">
                            @foreach($detailReport->evidences as $evidence)
                                <a href="{{ asset('storage/'.$evidence->file_path) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('storage/'.$evidence->file_path) }}" alt="Bukti foto {{ $detailReport->report_number }}" class="aspect-[1.55] w-full rounded-lg object-cover" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <div>
                        <p class="text-xs text-slate-600">Fasilitas</p>
                        <p class="mt-1 text-sm font-semibold">{{ $detailReport?->facility?->name ?? '―' }}@if($detailReport?->specific_location) · {{ $detailReport->specific_location }}@endif</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $detailReport?->description }}</p>
                        @if(! $detailReport?->evidences->isNotEmpty())<p class="mt-2 text-xs text-slate-600">Belum ada foto bukti.</p>@endif
                    </div>
                </div>
            </div>
            <dl class="grid content-start gap-3 text-sm">
                <div><dt class="text-xs text-slate-600">Petugas pemeriksa</dt><dd class="mt-1 font-semibold">{{ $detail->actor?->name ?? $detail->verification?->verifier?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-600">Waktu pemeriksaan</dt><dd class="mt-1 font-semibold">{{ $detail->changed_at?->translatedFormat('d F Y, H.i') ?? '—' }}@if($detail->changed_at) WIB @endif</dd></div>
                <div><dt class="text-xs text-slate-600">Hasil keputusan</dt><dd class="mt-1 font-semibold">{{ $detailAccepted ? 'Diterima' : 'Ditolak' }}</dd></div>
            </dl>
        </div>
        <div>
            <h3 class="mb-2 text-sm font-semibold">Catatan verifikasi</h3>
            <p class="rounded-lg border border-slate-300 bg-slate-50 p-4 text-sm leading-6 text-slate-700">{{ $detail->notes ?: 'Belum ada catatan.' }}</p>
        </div>
        <p class="text-xs text-slate-600">Keputusan tercatat di riwayat laporan.</p>
    </section>
@endif
@endsection
