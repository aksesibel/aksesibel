
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
    @if($reports->isEmpty())
        <div class="text-center py-5">
            <h5 class="fw-semibold">Belum Ada Laporan</h5>
            <p class="text-muted">
                Anda belum memiliki laporan kendala.
            </p>
            <a href="{{ route('reports.create') }}" class="btn btn-primary">
                Buat Laporan Pertama
            </a>
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
                                <span class="badge bg-warning text-dark">
                                    {{ $report->status->status_name ?? '-' }}
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
    