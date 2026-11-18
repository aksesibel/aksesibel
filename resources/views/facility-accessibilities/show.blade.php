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
                <img src="{{ asset('storage/' . $facilityAccessibility->photo_path) }}" alt="Foto aksesibilitas"
                    style="max-width: 300px; border-radius: 8px;">
            </div>
        @endif
    </div>

    {{-- Form Pemeriksaan PBI-06 --}}
    <div class="card">
        <h3>Tambah Pemeriksaan</h3>
        <p>Catat hasil pemeriksaan kondisi sarana aksesibilitas.</p>

        <form
            action="{{ route('facility-accessibilities.inspections.store', $facilityAccessibility->facility_accessibility_id) }}"
            method="POST">
            @csrf

            <div class="field">
                <label for="inspection_date">Tanggal Pemeriksaan</label>
                <input type="date" id="inspection_date" name="inspection_date"
                    value="{{ old('inspection_date', date('Y-m-d')) }}" required>
                @error('inspection_date')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="field">
                <label for="condition_result">Hasil Kondisi</label>
                <select id="condition_result" name="condition_result" required>
                    <option value="">Pilih kondisi</option>

                    @foreach (\App\Models\FacilityAccessibility::CONDITION_LABELS as $value => $label)
                        <option value="{{ $value }}" {{ old('condition_result') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                @error('condition_result')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="field">
                <label for="notes">Catatan Pemeriksaan</label>
                <textarea id="notes" name="notes" rows="3"
                    placeholder="Tuliskan catatan hasil pemeriksaan...">{{ old('notes') }}</textarea>

                @error('notes')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="field">
                <label for="information_source">Sumber Informasi</label>
                <input type="text" id="information_source" name="information_source" value="{{ old('information_source') }}"
                    maxlength="255" placeholder="Contoh: Pemeriksaan langsung, laporan pengelola">

                @error('information_source')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Simpan Pemeriksaan
                </button>
            </div>
        </form>
    </div>


    {{-- Riwayat Pemeriksaan --}}
    <div class="card">
        <div class="page-header">
            <div>
                <h3>Riwayat Pemeriksaan</h3>
                <p>Daftar hasil pemeriksaan kondisi sarana aksesibilitas.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($inspections->isEmpty())
            <p>Belum ada data pemeriksaan.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal Pemeriksaan</th>
                            <th>Pemeriksa</th>
                            <th>Hasil Kondisi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($inspections as $index => $inspection)
                            <tr>
                                <td>{{ $inspections->firstItem() + $index }}</td>

                                <td>
                                    {{ $inspection->inspection_date->format('d-m-Y') }}
                                </td>

                                <td>
                                    {{ $inspection->inspector->name ?? '-' }}
                                </td>

                                <td>
                                    @php
                                        $conditionLabel = \App\Models\FacilityAccessibility::CONDITION_LABELS[$inspection->condition_result] ?? '-';
                                    @endphp

                                    {{ $conditionLabel }}
                                </td>

                                <td>
                                    <details>
                                        <summary style="cursor: pointer; color: #176B55;">
                                            Lihat Detail
                                        </summary>

                                        <div style="margin-top: 10px; min-width: 180px;">
                                            <strong>Catatan Pemeriksaan</strong>
                                            <p>{{ $inspection->notes ?: '-' }}</p>

                                            <strong>Sumber Informasi</strong>
                                            <p>{{ $inspection->information_source ?: '-' }}</p>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 16px;">
                {{ $inspections->links() }}
            </div>
        @endif
    </div>

    <div class="form-actions">
        <a href="{{ route('facility-accessibilities.edit', $facilityAccessibility->facility_accessibility_id) }}"
            class="btn btn-primary">
            Edit Data
        </a>
    </div>

@endsection