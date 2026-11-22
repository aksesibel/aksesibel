@extends('layouts.app')

@section('title', 'Kelola Status Verifikasi')

@section('breadcrumb', 'Aksesibel / Kelola status')

@section('content')
<div class="status-page">

    <!-- HEADER -->
    <div class="page-heading">
        <h1>Kelola Status Verifikasi</h1>
        <p>Pantau tahapan laporan masyarakat dan perbarui status verifikasinya.</p>
    </div>

    <!-- STATISTIK -->
    <div class="status-stats">

        <div class="stat-card">
            <div class="stat-top">
                <span>Menunggu verifikasi</span>
                <i class="fa-regular fa-clipboard stat-icon"></i>
            </div>
            <h2>{{ $statistik['menunggu'] }}</h2>
            <p>Laporan belum diperiksa</p>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span>Terverifikasi</span>
                <i class="fa-regular fa-circle-check stat-icon"></i>
            </div>
            <h2>{{ $statistik['terverifikasi'] }}</h2>
            <p>Laporan telah dinyatakan sesuai</p>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span>Ditolak</span>
                <i class="fa-regular fa-circle-xmark stat-icon"></i>
            </div>
            <h2>{{ $statistik['ditolak'] }}</h2>
            <p>Laporan tidak memenuhi kriteria</p>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span>Memerlukan pemeriksaan lanjutan</span>
                <i class="fa-solid fa-shield-halved stat-icon"></i>
            </div>
            <h2>{{ $statistik['lanjutan'] }}</h2>
            <p>Perlu pengecekan tambahan</p>
        </div>

    </div>

    <!-- DETAIL DAN FORM STATUS -->
    @if ($detail)
    <div class="status-panel">

        <div class="panel-heading">
            <h2>Detail laporan dan perubahan status</h2>
            <span class="report-badge">
                {{ $detail->report_number }}
            </span>
        </div>

        <div class="report-detail-grid">

            <div class="report-main-detail">
                <h3>{{ $detail->description }}</h3>

                <div class="report-meta">
                    <div>
                        <label>Fasilitas</label>
                        <strong>{{ $detail->facility?->name ?? '-' }}</strong>
                    </div>

                    <div>
                        <label>Tanggal laporan</label>
                        <strong>
                            {{ $detail->created_at?->format('d F Y, H.i') ?? '-' }} WIB
                        </strong>
                    </div>
                </div>

                <p class="report-description">
                    {{ $detail->description }}
                </p>
            </div>

            <div class="report-current-status">
                <div>
                    <label>Status saat ini</label>
                    <span class="status-badge
                            @if ($detail->status?->status_name == 'Terverifikasi') verified
                            @elseif ($detail->status?->status_name == 'Ditolak') rejected
                            @elseif ($detail->status?->status_name == 'Dalam Tindak Lanjut') follow-up
                            @else waiting
                            @endif">
                        {{ $detail->status?->status_name ?? '-' }}
                    </span>
                </div>

                <div>
                    <label>Terakhir diperbarui</label>
                    <strong>
                        {{ $detail->updated_at?->format('d F Y, H.i') ?? '-' }} WIB
                    </strong>
                </div>
            </div>

        </div>

        <!-- FORM PERUBAHAN STATUS -->
        <form
            action="{{ route('verifikasi.status.update', $detail->report_id) }}"
            method="POST"
            class="status-form">
            @csrf

            <label class="form-title">Ubah status menjadi</label>

            <div class="status-options">
                @foreach ($daftarStatus as $status)
                <label class="status-option">
                    <input
                        type="radio"
                        name="status_id"
                        value="{{ $status->status_id }}"
                        {{ $detail->status_id == $status->status_id ? 'checked' : '' }}
                        required>

                    <span>{{ $status->status_name }}</span>
                </label>
                @endforeach
            </div>

            <label for="notes" class="form-title">
                Catatan perubahan status
            </label>

            <textarea
                name="notes"
                id="notes"
                rows="3"
                placeholder="Tuliskan alasan atau catatan perubahan status...">{{ old('notes') }}</textarea>

            @error('status_id')
            <small class="form-error">{{ $message }}</small>
            @enderror

            @error('notes')
            <small class="form-error">{{ $message }}</small>
            @enderror

            <div class="form-footer">
                <p>Perubahan status dan catatan akan tercatat setelah disimpan.</p>

                <div class="form-actions">
                    <a
                        href="{{ route('verifikasi.status') }}"
                        class="btn-cancel">
                        Batal
                    </a>

                    <button type="submit" class="btn-save">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

    </div>
    @else
    <div class="status-panel empty-detail">
        <p>Belum ada laporan yang dapat ditampilkan.</p>
    </div>
    @endif

    <!-- DAFTAR LAPORAN -->
    <div class="status-panel report-list-panel">

        <div class="list-heading">
            <h2>Daftar status laporan</h2>
            <span class="total-badge">
                {{ $laporan->total() }} laporan
            </span>
        </div>

        <!-- FILTER -->
        <form action="{{ route('verifikasi.status') }}" method="GET" class="status-filter">

            <div class="filter-group">
                <label for="search">Cari laporan</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nomor laporan atau fasilitas">
            </div>

            <div class="filter-group">
                <label for="status">Status verifikasi</label>
                <select name="status" id="status">
                    <option value="">Semua status</option>
                    @foreach ($daftarStatus as $status)
                    <option
                        value="{{ $status->status_id }}"
                        {{ request('status') == $status->status_id ? 'selected' : '' }}>
                        {{ $status->status_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="tanggal">Tanggal laporan</label>
                <input
                    type="date"
                    name="tanggal"
                    id="tanggal"
                    value="{{ request('tanggal') }}">
            </div>

            <button type="submit" class="filter-submit">Cari</button>
        </form>

        <!-- TABEL -->
        <div class="table-wrapper">
            <table class="status-table">
                <thead>
                    <tr>
                        <th>Nomor laporan</th>
                        <th>Uraian laporan</th>
                        <th>Fasilitas</th>
                        <th>Tanggal laporan</th>
                        <th>Status saat ini</th>
                        <th>Terakhir diperbarui</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($laporan as $item)
                    <tr class="{{ $detail && $detail->report_id == $item->report_id ? 'active-row' : '' }}">
                        <td>
                            <strong class="table-report-number">
                                {{ $item->report_number }}
                            </strong>
                        </td>

                        <td class="description-cell">
                            {{ $item->description }}
                        </td>

                        <td>
                            {{ $item->facility?->name ?? '-' }}
                        </td>

                        <td>
                            {{ $item->created_at?->format('d M Y') ?? '-' }}
                        </td>

                        <td>
                            <span class="status-badge
                                    @if ($item->status?->status_name == 'Terverifikasi') verified
                                    @elseif ($item->status?->status_name == 'Ditolak') rejected
                                    @elseif ($item->status?->status_name == 'Dalam Tindak Lanjut') follow-up
                                    @else waiting
                                    @endif">
                                {{ $item->status?->status_name ?? '-' }}
                            </span>
                        </td>

                        <td>
                            {{ $item->updated_at?->format('d M Y H.i') ?? '-' }} WIB
                        </td>

                        <td>
                            <a
                                href="{{ route('verifikasi.status', array_merge(request()->except('kelola', 'page'), ['kelola' => $item->report_id])) }}"
                                class="btn-manage">
                                Kelola
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty-table">
                            Tidak ada laporan yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="table-footer">
            <p>
                Menampilkan {{ $laporan->firstItem() ?? 0 }}–{{ $laporan->lastItem() ?? 0 }}
                dari {{ $laporan->total() }} laporan
            </p>

            {{ $laporan->links() }}
        </div>

    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/status-verifikasi.css') }}">
@endpush