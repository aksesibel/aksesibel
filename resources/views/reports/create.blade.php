
@extends('layouts.masyarakat')

@section('title', 'Buat Laporan')
@section('breadcrumb', 'Buat Laporan')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1">Buat Laporan Kendala</h4>
    <p class="text-muted">
        Sampaikan kendala aksesibilitas fasilitas publik agar dapat ditindaklanjuti.
    </p>
</div>

@if(session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Periksa kembali data Anda.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-card">
    <div class="mb-4">
        <div class="d-flex justify-content-between text-muted small mb-2">
            <span>Informasi Laporan</span>
            <span>Formulir Laporan</span>
        </div>
        <div class="progress" style="height: 6px;">
            <div class="progress-bar" style="width: 100%;"></div>
        </div>
    </div>

    @if(!$activePeriod)
        <div class="alert alert-warning">
            Periode pelaporan saat ini belum tersedia. Silakan hubungi pengelola.
        </div>
    @endif

    @if($facilities->isEmpty())
        <div class="alert alert-warning">
            Belum ada data fasilitas aktif yang dapat dilaporkan.
        </div>
    @endif

    @if($categories->isEmpty())
        <div class="alert alert-warning">
            Belum ada kategori kendala aktif.
        </div>
    @endif

    <form action="{{ route('reports.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf

        <h6 class="fw-bold mb-3">Informasi Kendala</h6>

        <div class="mb-3">
            <label class="form-label fw-semibold">Fasilitas Publik *</label>
            <select name="facility_id" class="form-select" required>
                <option value="">Pilih fasilitas</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->facility_id }}"
                        @selected(old('facility_id') == $facility->facility_id)>
                        {{ $facility->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">
                Item Aksesibilitas
            </label>

            <select name="facility_accessibility_id"
                    id="facility_accessibility_id"
                    class="form-select"
                    disabled>
                <option value="">
                    Pilih fasilitas terlebih dahulu
                </option>
            </select>

            <div class="form-text">
                Pilih item aksesibilitas pada fasilitas yang mengalami kendala.
                Pilihan ini bersifat opsional.
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Kategori Kendala *</label>
            <select name="issue_category_id" class="form-select" required>
                <option value="">Pilih kategori kendala</option>
                @foreach($categories as $category)
                    <option value="{{ $category->issue_category_id }}"
                        @selected(old('issue_category_id') == $category->issue_category_id)>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Judul Laporan *</label>
            <input type="text"
                   name="report_title"
                   class="form-control"
                   maxlength="255"
                   value="{{ old('report_title') }}"
                   placeholder="Contoh: Jalur khusus kursi roda terhalang"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Tanggal Kejadian *</label>
            <input type="date"
                   name="incident_date"
                   class="form-control"
                   max="{{ now()->toDateString() }}"
                   value="{{ old('incident_date') }}"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Lokasi Spesifik</label>
            <textarea name="specific_location"
                      class="form-control"
                      rows="2"
                      placeholder="Contoh: Pintu masuk sisi timur lantai 1">{{ old('specific_location') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi Kendala *</label>
            <textarea name="description"
                      class="form-control"
                      rows="5"
                      maxlength="5000"
                      placeholder="Jelaskan kendala yang ditemukan secara terperinci..."
                      required>{{ old('description') }}</textarea>
        </div>

        <hr class="my-4">

        <h6 class="fw-bold mb-3">Bukti Pendukung</h6>

        <div class="mb-3">
            <label class="form-label fw-semibold">Unggah Foto</label>
            <input type="file"
                   name="evidences[]"
                   class="form-control"
                   accept=".jpg,.jpeg,.png,.webp"
                   multiple>
            <div class="form-text">
                Maksimal 5 foto, format JPG, JPEG, PNG, atau WEBP.
                Maksimal 2 MB per foto.
            </div>
        </div>

        <hr class="my-4">

        <div class="form-check mb-4">
            <input class="form-check-input"
                   type="checkbox"
                   name="is_anonymous"
                   value="1"
                   id="is_anonymous"
                   @checked(old('is_anonymous'))>
            <label class="form-check-label" for="is_anonymous">
                Sembunyikan nama saya pada tampilan laporan publik.
            </label>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('reports.index') }}"
               class="btn btn-outline-secondary">
                Batal
            </a>
            <button type="submit"
                    class="btn btn-primary"
                    @disabled(!$activePeriod || $facilities->isEmpty() || $categories->isEmpty())>
                Kirim Laporan
            </button>
        </div>
    </form>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const facilitySelect = document.getElementById('facility_id');
    const accessibilitySelect = document.getElementById('facility_accessibility_id');

    const oldAccessibilityId = @json(old('facility_accessibility_id'));

    const endpointTemplate = @json(
        route('reports.facilities.accessibilities', ['facility' => '__FACILITY_ID__'])
    );

    async function loadAccessibilities(facilityId) {
        accessibilitySelect.innerHTML = '';
        accessibilitySelect.disabled = true;

        if (!facilityId) {
            accessibilitySelect.innerHTML =
                '<option value="">Pilih fasilitas terlebih dahulu</option>';
            return;
        }

        accessibilitySelect.innerHTML =
            '<option value="">Memuat item aksesibilitas...</option>';

        try {
            const endpoint = endpointTemplate.replace(
                '__FACILITY_ID__',
                facilityId
            );

            const response = await fetch(endpoint);

            if (!response.ok) {
                throw new Error('Gagal mengambil data');
            }

            const items = await response.json();

            accessibilitySelect.innerHTML =
                '<option value="">Pilih item aksesibilitas (opsional)</option>';

            if (items.length === 0) {
                accessibilitySelect.innerHTML =
                    '<option value="">Belum ada item aksesibilitas</option>';
                return;
            }

            items.forEach(function (item) {
                const option = document.createElement('option');

                option.value = item.id;

                let label = item.name;

                if (item.location) {
                    label += ' - ' + item.location;
                }

                option.textContent = label;

                if (String(item.id) === String(oldAccessibilityId)) {
                    option.selected = true;
                }

                accessibilitySelect.appendChild(option);
            });

            accessibilitySelect.disabled = false;

        } catch (error) {
            accessibilitySelect.innerHTML =
                '<option value="">Gagal memuat item aksesibilitas</option>';

            console.error(error);
        }
    }

    facilitySelect.addEventListener('change', function () {
        loadAccessibilities(this.value);
    });

    // Muat ulang otomatis jika halaman kembali karena validasi.
    if (facilitySelect.value) {
        loadAccessibilities(facilitySelect.value);
    }
});
</script>
@endpush

