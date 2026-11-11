@extends('layouts.app')

@section('title', 'Antrean Verifikasi | Aksesibel')

@section('breadcrumb', 'Aksesibel / Antrean verifikasi')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/verifikasi.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <h1>Verifikasi laporan masyarakat</h1>
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
            Diverifikasi hari ini
            <i class="fa-regular fa-shield-check"></i>
        </div>
        <h2>{{ $statistik['terverifikasi'] }}</h2>
        <small>Siap diteruskan ke pengelola</small>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            Ditolak bulan ini
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

        <div class="queue-search">
            <label for="searchReport">Cari nomor atau fasilitas</label>
            <div class="search-box">
                <input type="text" id="searchReport" placeholder="Cari laporan">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
        </div>

        <div class="queue-tabs">
            <button class="queue-tab active" id="tabMasuk">
                Masuk ({{ $laporan->total() }})
            </button>
            <button class="queue-tab" id="tabRiwayat">Riwayat</button>
        </div>

        <div class="report-list" id="reportList">

            @forelse ($laporan as $index => $item)
            <article
                class="report-card {{ $index === 0 ? 'active' : '' }}"
                data-index="{{ $index }}"
                data-id="{{ $item->report_number }}"
                data-title="{{ $item->description }}"
                data-facility="{{ $item->facility->name ?? '-' }}"
                data-location="{{ $item->facility->address ?? '-' }}"
                data-reporter="{{ $item->user->name ?? '-' }}"
                data-date="{{ $item->created_at?->format('d M Y, H.i') }}"
                data-description="{{ $item->description }}"
                data-filename="{{ $item->evidences->first()->file_path ?? '' }}">
                <div class="report-number">
                    {{ $item->report_number }}
                </div>

                <h3>{{ $item->description }}</h3>

                <div class="report-facility">
                    {{ $item->facility->name ?? '-' }}
                </div>

                <span class="report-tag">
                    <i class="fa-regular fa-circle-dot"></i>
                    {{ $item->status->status_name ?? 'Laporan masuk' }}
                </span>

                <div class="report-date">
                    {{ $item->created_at?->format('d M Y, H.i') }}
                </div>
            </article>
            @empty
            <div class="empty-state">
                Belum ada laporan yang menunggu verifikasi.
            </div>
            @endforelse

        </div>

        <div class="queue-info" id="queueInfo">
            Menampilkan {{ $laporan->count() }} dari {{ $laporan->total() }} laporan
        </div>

        <button class="load-more" onclick="showToast('Semua data dummy sudah ditampilkan')">
            Muat laporan berikutnya
        </button>

    </section>

    <!-- KOLOM KANAN: DETAIL -->
    <section class="detail-column">

        <div class="detail-panel">

            <div class="detail-heading">
                <div>
                    <div class="detail-id" id="detailId">AKS-261014-024</div>
                    <h2 id="detailTitle">Jalur pemandu terhalang pot tanaman</h2>
                </div>

                <span class="incoming-tag">
                    <i class="fa-regular fa-circle-dot"></i>
                    Laporan masuk
                </span>
            </div>

            <div class="report-meta">
                <div>
                    Pelapor: <span id="detailReporter">Rina W. (privat)</span>
                </div>
                <div>
                    Masuk: <span id="detailDate">14 Okt 2026, 11.40</span>
                </div>
            </div>

            <div class="facility-name">
                <span id="detailFacility">Taman Literasi Martha Tiahahu</span>
                • <span id="detailLocation">Pintu masuk utara</span>
            </div>

            <p class="description" id="detailDescription">
                Dua pot tanaman menutupi jalur pemandu dekat pintu masuk utara.
                Pengunjung perlu keluar dari jalur untuk melewatinya.
                Mohon pot dipindahkan agar jalur dapat digunakan kembali.
            </p>

            <h3 class="section-title">Bukti yang dilampirkan</h3>

            <img
                id="evidenceImage"
                class="evidence-image"
                src="https://images.unsplash.com/photo-1519331379826-f10be5486c6f?auto=format&fit=crop&w=1200&q=85"
                alt="Bukti foto laporan fasilitas">

            <div class="evidence-footer">
                <span>
                    <span id="detailFilename">jalur-pemandu.jpg</span>
                    • <span id="detailSize">1,8 MB</span>
                    • <span id="detailDateFile">14 Okt 2026</span>
                </span>

                <button class="zoom-btn" onclick="openEvidence()">
                    <i class="fa-solid fa-expand"></i>
                    Perbesar foto
                </button>
            </div>

            <h3 class="section-title" style="margin-top:22px;">
                Daftar pemeriksaan
            </h3>

            <div class="checklist">
                <label>
                    <input type="checkbox" class="inspection-check">
                    Fasilitas dan jenis sarana sesuai
                </label>

                <label>
                    <input type="checkbox" class="inspection-check">
                    Lokasi kejadian dapat dikenali
                </label>

                <label>
                    <input type="checkbox" class="inspection-check">
                    Bukti menunjukkan kendala yang dilaporkan
                </label>
            </div>

        </div>

        <!-- KEPUTUSAN -->
        <div class="decision-panel">

            <div class="decision-heading">
                <h2>Keputusan verifikasi</h2>
                <span class="unsent" id="decisionStatus">Belum dikirim</span>
            </div>

            <div class="decision-options">
                <button
                    type="button"
                    class="decision-option"
                    id="approveOption"
                    onclick="selectDecision('approve')">
                    <i class="fa-regular fa-circle"></i>
                    Terima & verifikasi
                </button>

                <button
                    type="button"
                    class="decision-option active reject"
                    id="rejectOption"
                    onclick="selectDecision('reject')">
                    <i class="fa-solid fa-circle"></i>
                    Tolak laporan
                </button>
            </div>

            <div id="reasonSection">
                <label class="form-label" for="rejectionReason">
                    Alasan penolakan *
                </label>

                <textarea
                    id="rejectionReason"
                    class="reason-input"
                    placeholder="Tuliskan alasan penolakan laporan...">Foto belum memperlihatkan pot yang menghalangi jalur secara jelas. Mohon kirim foto lebih dekat yang menunjukkan titik kendala di pintu masuk utara.</textarea>

                <p class="helper-text">
                    Alasan ini akan ditampilkan kepada pelapor. Gunakan bahasa jelas dan sopan.
                </p>
            </div>

            <div class="warning-box" id="decisionNotice">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                    <strong>Pelapor akan menerima alasan penolakan</strong>
                    <p>Laporan yang ditolak tidak diteruskan untuk penugasan. Kondisi fasilitas tidak berubah.</p>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-outline" onclick="saveInspection()">
                    Simpan pemeriksaan
                </button>

                <button class="btn btn-reject" id="submitDecision" onclick="submitDummyDecision()">
                    <i class="fa-regular fa-circle-xmark"></i>
                    Tolak laporan
                </button>
            </div>

        </div>

    </section>

</div>

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script src="{{ asset('js/verifikasi.js') }}"></script>
@endpush