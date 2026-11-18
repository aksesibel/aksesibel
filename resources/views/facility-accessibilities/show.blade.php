
@extends('layout.pengelola')

@section('title', 'Detail Aksesibilitas Fasilitas')
@section('breadcrumb', 'Aksesibel / Data Aksesibilitas / Detail')

@section('content')

    <div class="page-header">
        <div>
            <h1>Detail Aksesibilitas Fasilitas</h1>
            <p>Informasi lengkap sarana aksesibilitas pada fasilitas.</p>
        </div>

        <a href="{{ route('facility-accessibilities.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    {{-- Informasi Aksesibilitas --}}
    <div class="card">
        <h3>Informasi Aksesibilitas</h3>

        <div class="detail-grid">
            <div class="field">
                <label>Nama Fasilitas</label>
                <p>{{ $facilityAccessibility->facility->name ?? '-' }}</p>
            </div>

            <div class="field">
                <label>Jenis Aksesibilitas</label>
                <p>{{ $facilityAccessibility->accessibilityType->type_name ?? '-' }}</p>
            </div>

            <div class="field">
                <label>Ketersediaan</label>
                <p>
                    {{ \App\Models\FacilityAccessibility::AVAILABILITY_LABELS[$facilityAccessibility->availability_status] ?? '-' }}
                </p>
            </div>

            <div class="field">
                <label>Kondisi</label>
                <p>
                    {{ \App\Models\FacilityAccessibility::CONDITION_LABELS[$facilityAccessibility->condition_status] ?? '-' }}
                </p>
            </div>

            <div class="field">
                <label>Lokasi</label>
                <p>{{ $facilityAccessibility->location_description ?? '-' }}</p>
            </div>

            <div class="field">
                <label>Catatan</label>
                <p>{{ $facilityAccessibility->notes ?? '-' }}</p>
            </div>
        </div>

        @if ($facilityAccessibility->photo_path)
            <div class="field">
                <label>Foto Aksesibilitas</label>
                <img
                    src="{{ asset('storage/' . $facilityAccessibility->photo_path) }}"
                    alt="Foto aksesibilitas"
                    style="max-width: 300px; border-radius: 8px;"
                >
            </div>
        @endif
    </div>

    {{-- Riwayat Pemeriksaan --}}
    <div class="card">
        <h3>Riwayat Pemeriksaan</h3>

        <p>Belum ada data pemeriksaan.</p>
    </div>

    <div class="form-actions">
        <a href="{{ route('facility-accessibilities.edit', $facilityAccessibility->facility_accessibility_id) }}"
            class="btn btn-primary">
            Edit Data
        </a>
    </div>

@endsection
