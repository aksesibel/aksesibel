
@extends('layouts.masyarakat')

@section('title', 'Detail Laporan')
@section('breadcrumb', 'Detail Laporan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Detail Laporan</h4>
        <p class="text-muted mb-0">{{ $report->report_number }}</p>
    </div>

    <a href="{{ route('reports.index') }}"
       class="btn btn-outline-secondary">
        Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="content-card mb-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="fw-bold">{{ $report->report_title }}</h5>
            <p class="text-muted mb-0">
                Dibuat {{ $report->created_at?->format('d M Y H:i') ?? '-' }}
            </p>
        </div>
        <span class="badge bg-warning text-dark">
            {{ $report->status->status_name ?? '-' }}
        </span>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <small class="text-muted">Fasilitas</small>
            <p class="fw-semibold">{{ $report->facility->name ?? '-' }}</p>
        </div>

        <div class="col-md-6">
            <small class="text-muted">Item Aksesibilitas</small>
            <p class="fw-semibold">
                {{ $report->facilityAccessibility?->accessibilityType?->type_name ?? 'Tidak terkait item aksesibilitas tertentu' }}
            </p>

            @if($report->facilityAccessibility?->location_description)
                <small class="text-muted">
                    {{ $report->facilityAccessibility->location_description }}
                </small>
            @endif
        </div>

        <div class="col-md-6">
            <small class="text-muted">Kategori Kendala</small>
            <p class="fw-semibold">
                {{ $report->issueCategory->category_name ?? '-' }}
            </p>
        </div>

        <div class="col-md-6">
            <small class="text-muted">Tanggal Kejadian</small>
            <p class="fw-semibold">
                {{ $report->incident_date?->format('d M Y') ?? '-' }}
            </p>
        </div>

        <div class="col-md-6">
            <small class="text-muted">Lokasi Spesifik</small>
            <p class="fw-semibold">{{ $report->specific_location ?? '-' }}</p>
        </div>

        <div class="col-12">
            <small class="text-muted">Deskripsi Kendala</small>
            <p style="white-space: pre-line;">{{ $report->description }}</p>
        </div>

        <div class="col-12">
            <small class="text-muted">Privasi</small>
            <p>
                {{ $report->is_anonymous ? 'Nama disembunyikan pada tampilan publik' : 'Nama tidak disembunyikan' }}
            </p>
        </div>
    </div>
</div>

<div class="content-card">
    <h5 class="fw-bold mb-3">Bukti Pendukung</h5>

    @if($report->evidences->isEmpty())
        <p class="text-muted mb-0">Belum ada bukti pendukung.</p>
    @else
        <div class="row g-3">
            @foreach($report->evidences as $evidence)
                <div class="col-md-4">
                    <a href="{{ asset('storage/' . $evidence->file_path) }}"
                       target="_blank">
                        <img src="{{ asset('storage/' . $evidence->file_path) }}"
                             alt="Bukti laporan"
                             class="img-fluid rounded border">
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
