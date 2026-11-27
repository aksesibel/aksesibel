@extends('layouts.app')

@section('title', 'Riwayat Verifikasi | Aksesibel')

@section('breadcrumb', 'Aksesibel / Riwayat verifikasi')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/riwayat-verifikasi.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <h1>Riwayat Verifikasi Laporan</h1>
    <p>Telusuri keputusan dan catatan pemeriksaan laporan masyarakat.</p>
</div>

<!-- STATISTIK -->
<div class="history-stats">

    <div class="history-stat-card">
        <div class="stat-label">
            Total diverifikasi
            <i class="fa-regular fa-clipboard"></i>
        </div>
        <h2>{{ $statistik['total'] }}</h2>
        <small>Seluruh laporan yang telah diperiksa</small>
    </div>

    <div class="history-stat-card">
        <div class="stat-label">
            Diterima
            <i class="fa-regular fa-shield-check"></i>
        </div>
        <h2>{{ $statistik['diterima'] }}</h2>
        <small>Laporan memenuhi kriteria</small>
    </div>

    <div class="history-stat-card">
        <div class="stat-label">
            Ditolak
            <i class="fa-regular fa-circle-xmark"></i>
        </div>
        <h2>{{ $statistik['ditolak'] }}</h2>
        <small>Laporan belum memenuhi kriteria</small>
    </div>

    <div class="history-stat-card">
        <div class="stat-label">
            Verifikasi hari ini
            <i class="fa-regular fa-calendar-check"></i>
        </div>
        <h2>{{ $statistik['hari_ini'] }}</h2>
        <small>{{ now()->translatedFormat('d F Y') }}</small>
    </div>

</div>

<!-- DAFTAR RIWAYAT -->
<div class="history-panel">

    <div class="history-panel-heading">
        <h2>Daftar riwayat verifikasi</h2>
        <span class="total-badge">{{ $riwayat->total() }} laporan</span>
    </div>

    <!-- FILTER -->
    <form action="{{ route('verifikasi.riwayat') }}"
        method="GET"
        class="history-filter">

        <div class="filter-search">
            <label for="search">Cari riwayat</label>
            <div class="search-input">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nomor laporan atau nama petugas">
            </div>
        </div>

        <div class="filter-group">
            <label for="hasil">Hasil verifikasi</label>
            <select name="hasil" id="hasil">
                <option value="">Semua hasil</option>
                <option value="Terverifikasi"
                    {{ request('hasil') == 'Terverifikasi' ? 'selected' : '' }}>
                    Diterima
                </option>
                <option value="Ditolak"
                    {{ request('hasil') == 'Ditolak' ? 'selected' : '' }}>
                    Ditolak
                </option>
            </select>
        </div>

        <div class="filter-group">
            <label for="tanggal">Tanggal pemeriksaan</label>
            <input
                type="date"
                name="tanggal"
                id="tanggal"
                value="{{ request('tanggal') }}">
        </div>

        <div class="filter-actions">
            <button type="submit" class="filter-button">
                Cari
            </button>
            <a href="{{ route('verifikasi.riwayat') }}" class="reset-button">
                Reset
            </a>
        </div>

    </form>

    <!-- TABEL -->
    <div class="table-wrapper">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Nomor laporan</th>
                    <th>Uraian laporan</th>
                    <th>Nama verifikator</th>
                    <th>Waktu pemeriksaan</th>
                    <th>Hasil verifikasi</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($riwayat as $item)
                @php
                $report = $item->verification?->report;
                $status = $item->newStatus?->status_name;
                $selected = $detail && $detail->history_id == $item->history_id;
                @endphp

                <tr class="{{ $selected ? 'selected-row' : '' }}">
                    <td>
                        <strong class="report-number">
                            {{ $report?->report_number ?? '-' }}
                        </strong>
                    </td>

                    <td class="description-cell">
                        {{ $report?->description ?? '-' }}
                    </td>

                    <td>
                        {{ $item->verification?->verifier?->name ?? $item->actor?->name ?? '-' }}
                    </td>

                    <td class="date-cell">
                        {{ $item->changed_at?->format('d M Y') ?? '-' }}
                        <br>
                        {{ $item->changed_at?->format('H.i') ?? '-' }} WIB
                    </td>

                    <td>
                        <span class="status-badge {{ $status == 'Terverifikasi' ? 'accepted' : 'rejected' }}">
                            {{ $status == 'Terverifikasi' ? 'Diterima' : 'Ditolak' }}
                        </span>
                    </td>

                    <td class="notes-cell">
                        {{ $item->notes ?: '-' }}
                    </td>

                    <td>
                        <a
                            href="{{ route('verifikasi.riwayat', array_merge(request()->query(), ['detail' => $item->history_id])) }}"
                            class="detail-button">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-row">
                        Belum ada riwayat verifikasi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="history-pagination">
        <span>
            Menampilkan {{ $riwayat->firstItem() ?? 0 }}–{{ $riwayat->lastItem() ?? 0 }}
            dari {{ $riwayat->total() }} laporan
        </span>

        {{ $riwayat->links() }}
    </div>

</div>

<!-- DETAIL -->
@if ($detail)
@php
$detailReport = $detail->verification?->report;
$detailStatus = $detail->newStatus?->status_name;
@endphp

<div class="history-detail">

    <div class="detail-panel-heading">
        <h2>Detail verifikasi laporan</h2>

        <span class="status-badge {{ $detailStatus == 'Terverifikasi' ? 'accepted' : 'rejected' }}">
            {{ $detailStatus == 'Terverifikasi' ? 'Diterima' : 'Ditolak' }}
        </span>
    </div>

    <div class="detail-content">

        <div class="detail-report">
            <span class="report-number">
                {{ $detailReport?->report_number ?? '-' }}
            </span>

            <h3>{{ $detailReport?->description ?? '-' }}</h3>

            <div class="evidence-facility">
                <div class="detail-evidence">
                    <img
                        id="evidenceImage"
                        class="evidence-image"
                        src="https://images.unsplash.com/photo-1519331379826-f10be5486c6f?auto=format&fit=crop&w=1200&q=85"
                        alt="Bukti foto laporan fasilitas">
                </div>

                <div class="facility-detail">
                    <strong>Fasilitas</strong>
                    <p>
                        {{ $detailReport?->facility?->name ?? '-' }}
                        @if ($detailReport?->facility?->address)
                        · {{ $detailReport->facility->address }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="detail-information">

            <div>
                <label>Petugas pemeriksa</label>
                <strong>
                    {{ $detail->verification?->verifier?->name ?? $detail->actor?->name ?? '-' }}
                </strong>
            </div>

            <div>
                <label>Waktu pemeriksaan</label>
                <strong>
                    {{ $detail->changed_at?->format('d F Y, H.i') ?? '-' }} WIB
                </strong>
            </div>

            <div>
                <label>Hasil keputusan</label>
                <strong>
                    {{ $detailStatus == 'Terverifikasi'
                            ? 'Diterima — diteruskan untuk tindak lanjut'
                            : 'Ditolak' }}
                </strong>
            </div>

        </div>

    </div>

    <div class="verification-notes">
        <label>Catatan verifikasi</label>
        <p>{{ $detail->notes ?: 'Tidak ada catatan verifikasi.' }}</p>
    </div>

    <small class="detail-footnote">
        Keputusan tercatat dalam riwayat dan tidak mengubah laporan asli.
    </small>

</div>
@endif

@endsection