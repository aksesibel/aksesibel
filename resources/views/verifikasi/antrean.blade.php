@extends('layouts.app')

@section('title', 'Antrean Verifikasi | Aksesibel')

@section('breadcrumb', 'Aksesibel / Antrean Verifikasi')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/verifikasi.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <h1>Verifikasi Laporan Masyarakat</h1>
    <p>Periksa kesesuaian fasilitas, uraian, dan bukti sebelum menetapkan keputusan.</p>
</div>

<!-- STATISTIK -->
<div class="stats">
    <div class="stat-card">
        <div class="stat-top">
            Menunggu verifikasi
            <i class="fa-regular fa-clipboard"></i>
        </div>
        <h2>{{ $statistik['menunggu'] }}</h2>
        <small>
            {{ $laporanHariIni }} masuk hari ini • {{ $laporanSebelumnya }} sebelumnya
        </small>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            Terverifikasi
            <i class="fa-regular fa-circle-check"></i>
        </div>
        <h2>{{ $statistik['terverifikasi'] }}</h2>
        <small>Siap diteruskan ke pengelola</small>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            Ditolak
            <i class="fa-regular fa-circle-xmark"></i>
        </div>
        <h2>{{ $statistik['ditolak'] }}</h2>
        <small>Setiap penolakan disertai alasan</small>
    </div>
</div>

<!-- WORKSPACE -->
<div class="workspace">

    <!-- KOLOM KIRI: ANTREAN -->
    <section class="queue-column">

        <form class="queue-search" action="{{ route('verifikasi.antrean') }}" method="GET">
            <label for="searchReport">Cari nomor atau fasilitas</label>
            <div class="search-box">
                <input type="search" id="searchReport" name="search" value="{{ request('search') }}" placeholder="Cari laporan">
                <button type="submit" aria-label="Cari laporan"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
            </div>
        </form>

        <div class="queue-tabs">
            <span class="queue-tab active">Masuk ({{ $laporan->total() }})</span>
            <a class="queue-tab" href="{{ route('verifikasi.riwayat') }}">Riwayat</a>
        </div>

        <div class="report-list" id="reportList">

            @forelse ($laporan as $index => $item)
            <article class="report-card {{ $index === 0 ? 'active' : '' }}">
                <div class="report-number">{{ $item->report_number }}</div>
                <h3>
                    <a href="{{ route('verifikasi.status', ['kelola' => $item->getKey()]) }}">
                        {{ $item->description }}
                    </a>
                </h3>
                <div class="report-facility">{{ $item->facility?->name ?? '-' }}</div>
                <span class="report-tag">
                    <i class="fa-regular fa-circle-dot" aria-hidden="true"></i>
                    {{ $item->status?->status_name ?? 'Laporan masuk' }}
                </span>
                <div class="report-date">{{ $item->created_at?->format('d M Y, H.i') }}</div>
            </article>
            @empty
            <div class="empty-state">Belum ada laporan yang menunggu verifikasi.</div>
            @endforelse

        </div>

        <div class="queue-info" id="queueInfo">
            Menampilkan {{ $laporan->count() }} dari {{ $laporan->total() }} laporan
        </div>

        <div class="queue-pagination">{{ $laporan->links() }}</div>
    </section>

    @if ($laporan->isNotEmpty())
    <section class="detail-column">
        <div class="detail-panel">
            <div class="detail-heading">
                <div>
                    <div class="detail-id">{{ $laporan->first()->report_number }}</div>
                    <h2>{{ $laporan->first()->description }}</h2>
                </div>
            </div>
            <p class="description">Buka laporan untuk memeriksa bukti dan mencatat keputusan verifikasi.</p>
            <a class="btn btn-primary" href="{{ route('verifikasi.status', ['kelola' => $laporan->first()->getKey()]) }}">
                Periksa laporan
            </a>
        </div>
    </section>
    @endif
</div>
@endsection
