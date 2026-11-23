
@extends('layouts.masyarakat')

@section('title', 'Laporan Saya')
@section('breadcrumb', 'Laporan Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Laporan Saya</h4>
        <p class="text-muted mb-0">
            Pantau laporan kendala fasilitas publik yang telah Anda kirim.
        </p>
    </div>

    <a href="{{ route('reports.create') }}" class="btn btn-primary">
        + Buat Laporan
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif

<div class="content-card">
    <form action="{{ route('reports.index') }}" method="GET" class="mb-4">
        <div class="row g-2">
            <div class="col-md-5">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari nomor, judul, atau fasilitas..."
                    value="{{ $search }}"
                >
            </div>

            <div class="col-md-4">
                <select name="status_id" class="form-select">
                    <option value="">Semua Status</option>

                    @foreach($statuses as $status)
                        <option value="{{ $status->status_id }}"
                            {{ (string) $statusId === (string) $status->status_id ? 'selected' : '' }}>
                            {{ $status->status_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="sort" class="form-select">
                    <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>
                        Terbaru
                    </option>

                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>
                        Terlama
                    </option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    Filter
                </button>

                @if($search !== '' || $statusId !== null || $sort === 'oldest')
                    <a href="{{ route('reports.index') }}"
                    class="btn btn-outline-secondary">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>
    @if($reports->isEmpty())
        <div class="text-center py-5">
            @if($search !== '' || $statusId !== null)
                <div class="mb-3">
                    <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                </div>

                <h5 class="fw-semibold">Laporan Tidak Ditemukan</h5>

                <p class="text-muted mb-3">
                    Tidak ada laporan yang sesuai dengan kata kunci
                    atau status yang dipilih.
                    Coba gunakan kata kunci atau filter lainnya.
                </p>

                <a href="{{ route('reports.index') }}"
                class="btn btn-outline-primary">
                    Reset Pencarian dan Filter
                </a>
            @else
                <div class="mb-3">
                    <i class="bi bi-clipboard-x text-muted"
                    style="font-size: 3rem;"></i>
                </div>

                <h5 class="fw-semibold">Belum Ada Laporan</h5>

                <p class="text-muted mb-3">
                    Anda belum memiliki laporan kendala fasilitas publik.
                    Silakan buat laporan jika menemukan kendala.
                </p>

                <a href="{{ route('reports.create') }}"
                class="btn btn-primary">
                    + Buat Laporan Pertama
                </a>
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nomor Laporan</th>
                        <th>Judul</th>
                        <th>Fasilitas</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->report_number }}</td>
                            <td>{{ $report->report_title ?? $report->report_number }}</td>
                            <td>{{ $report->facility->name ?? '-' }}</td>
                            <td>{{ $report->issueCategory->category_name ?? '-' }}</td>
                            <td>
                                @php
                                    $statusName = $report->status->status_name ?? '-';

                                    $statusClass = match ($statusName) {
                                        'Menunggu Verifikasi' => 'bg-warning text-dark',
                                        'Terverifikasi' => 'bg-success',
                                        'Ditolak' => 'bg-danger',
                                        'Dalam Tindak Lanjut' => 'bg-info text-dark',
                                        'Selesai' => 'bg-primary',
                                        default => 'bg-secondary',
                                    };
                                @endphp

                                <span class="badge {{ $statusClass }}">
                                    {{ $statusName }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('reports.show', $report->report_id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $reports->links() }}
    @endif
</div>
@endsection
    