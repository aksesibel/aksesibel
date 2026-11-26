@extends('layouts.masyarakat')

@section('title', 'Buat Laporan')
@section('breadcrumb', 'Buat Laporan')

@section('content')
<div class="page-heading">
    <div>
        <h1>Buat laporan kendala akses</h1>
        <p>Ceritakan kondisi yang Anda temukan agar dapat ditindaklanjuti.</p>
    </div>
</div>

@if(session('info'))<p class="information-note" role="status">{{ session('info') }}</p>@endif

<nav class="form-stepper" aria-label="Bagian formulir">
    <a href="#pilih-fasilitas"><span>1</span> Pilih fasilitas</a>
    <a href="#jelaskan-kendala"><span>2</span> Jelaskan kendala</a>
    <a href="#bukti-privasi"><span>3</span> Bukti &amp; privasi</a>
</nav>

<div class="grid items-start gap-5 xl:grid-cols-[minmax(0,2.7fr)_minmax(0,1fr)]">
    <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" id="report-form" class="grid min-w-0 gap-5">
        @csrf
        <section id="pilih-fasilitas" class="panel grid scroll-mt-24 gap-4" aria-labelledby="report-location">
            <div><h2 id="report-location">Lokasi dan kendala</h2><p class="mt-1 text-xs text-slate-600">Kolom bertanda * wajib diisi.</p></div>

            @if(!$activePeriod)<p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900" role="status">{{ $periodError }}</p>@endif
            @if($facilities->isEmpty())<p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900">Belum ada data fasilitas aktif yang dapat dilaporkan.</p>@endif
            @if($categories->isEmpty())<p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900">Belum ada kategori kendala aktif.</p>@endif

            <div>
                <label for="facility_id">Fasilitas publik *</label>
                <select name="facility_id" id="facility_id" data-accessibility-endpoint="{{ route('reports.facilities.accessibilities', ['facility' => '__FACILITY_ID__']) }}" class="field" required>
                    <option value="">Pilih fasilitas</option>
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->facility_id }}" @selected(old('facility_id') == $facility->facility_id)>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid items-start gap-4 sm:grid-cols-2">
                <div>
                    <label for="facility_accessibility_id">Sarana terkait</label>
                    <select name="facility_accessibility_id" id="facility_accessibility_id" class="field disabled:bg-slate-100" aria-describedby="sarana-help" disabled>
                        <option value="">Pilih fasilitas terlebih dahulu</option>
                    </select>
                    <p id="sarana-help" class="mt-1 text-xs text-slate-600">Opsional jika kendala tidak terkait sarana tertentu.</p>
                </div>
                <div>
                    <label for="incident_date">Tanggal kejadian *</label>
                    <input id="incident_date" type="date" name="incident_date" class="field" max="{{ now()->toDateString() }}" value="{{ old('incident_date') }}" required>
                </div>
            </div>

            <div id="jelaskan-kendala" class="grid scroll-mt-24 gap-4">
                <div>
                    <label for="issue_category_id">Kategori kendala *</label>
                    <select id="issue_category_id" name="issue_category_id" class="field" required>
                        <option value="">Pilih kategori kendala</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->issue_category_id }}" @selected(old('issue_category_id') == $category->issue_category_id)>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="report_title">Judul laporan *</label>
                    <input id="report_title" name="report_title" class="field" maxlength="255" value="{{ old('report_title') }}" placeholder="Contoh: Jalur pemandu terhalang pot tanaman" required>
                </div>
                <div>
                    <label for="specific_location">Lokasi sarana di fasilitas</label>
                    <textarea id="specific_location" name="specific_location" class="field" rows="2" maxlength="2000" placeholder="Contoh: Jalur menuju pintu masuk utara">{{ old('specific_location') }}</textarea>
                </div>
                <div>
                    <label for="description">Uraian kendala *</label>
                    <textarea id="description" name="description" class="field" rows="5" maxlength="5000" placeholder="Jelaskan kondisi dan dampaknya bagi pengguna" aria-describedby="description-help description-count" required>{{ old('description') }}</textarea>
                    <div class="mt-1 flex flex-wrap justify-between gap-2 text-xs text-slate-600">
                        <span id="description-help">Sebutkan lokasi tanpa menyertakan data pribadi.</span>
                        <span id="description-count">0/5000 karakter</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="bukti-privasi" class="panel grid scroll-mt-24 gap-4" aria-labelledby="evidence-heading">
            <div>
                <h2 id="evidence-heading">Bukti pendukung</h2>
                <p class="mt-1 text-sm text-slate-600">Foto yang jelas membantu verifikator memahami kondisi.</p>
            </div>
            <label for="evidences" class="upload-area">
                <x-icon name="report" :size="23" />
                <span>Tambah foto bukti</span>
                <span class="text-xs font-normal text-slate-600">JPG, PNG, atau WEBP · Maksimal 2 MB per foto · Hingga 5 foto</span>
                <input type="file" name="evidences[]" id="evidences" class="absolute inset-0 size-full cursor-pointer opacity-0" accept=".jpg,.jpeg,.png,.webp" aria-describedby="evidence-count evidence-error" multiple>
            </label>
            <div id="evidence-preview" class="grid grid-cols-2 gap-3 md:grid-cols-3"></div>
            <p id="evidence-count" class="text-xs text-slate-600" aria-live="polite">0 dari 5 foto dipilih</p>
            <div id="evidence-error" class="text-sm text-red-700" role="alert"></div>
        </section>

        <section class="panel grid gap-4" aria-labelledby="privacy-heading">
            <h2 id="privacy-heading">Privasi laporan</h2>
            <label class="flex items-start gap-3 text-sm font-normal" for="is_anonymous">
                <input class="mt-0.5 size-4 accent-blue-700" type="checkbox" name="is_anonymous" value="1" id="is_anonymous" @checked(old('is_anonymous'))>
                Sembunyikan nama saya pada tampilan laporan publik.
            </label>
            <p class="text-xs text-slate-600">Nama tetap dapat dilihat oleh petugas yang berwenang menangani laporan.</p>
            <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-4">
                <a href="{{ route('reports.index') }}" class="button-secondary">Batal</a>
                <button type="submit" id="submit-report" class="button disabled:cursor-not-allowed disabled:opacity-50" @disabled(!$activePeriod || $facilities->isEmpty() || $categories->isEmpty())>
                    <x-icon name="arrow" :size="16" />
                    <span id="submit-text">Kirim Laporan</span>
                    <span id="submit-loading" class="hidden">Mengirim laporan…</span>
                </button>
            </div>
        </section>
    </form>

    <aside class="grid gap-4" aria-label="Panduan laporan">
        <section class="panel !bg-blue-50">
            <x-icon name="report" class="mb-3 text-primary" :size="22" />
            <h2>Laporan yang membantu</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-4 text-sm leading-6 text-slate-600">
                <li>Sebutkan titik sarana dengan jelas.</li>
                <li>Foto kondisi dari sudut yang aman.</li>
                <li>Jelaskan dampaknya bagi pengguna.</li>
            </ol>
        </section>
        <section class="panel">
            <h2>Setelah dikirim</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Laporan masuk ke antrean verifikasi. Anda mendapat nomor laporan dan dapat memantau statusnya di <a href="{{ route('reports.index') }}" class="font-semibold text-primary hover:underline">Laporan saya</a>.</p>
        </section>
        <div class="information-note"><x-icon name="info" :size="18" /><p><strong>Bukan layanan darurat.</strong> Untuk risiko keselamatan segera, hubungi petugas fasilitas di lokasi.</p></div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const facilitySelect = document.getElementById('facility_id');
    const accessibilitySelect = document.getElementById('facility_accessibility_id');
    // Preview foto bukti laporan sebelum diunggah.
    const evidenceInput = document.getElementById('evidences');
    const evidencePreview = document.getElementById('evidence-preview');
    const evidenceCount = document.getElementById('evidence-count');
    const evidenceError = document.getElementById('evidence-error');

    // Konfirmasi sebelum laporan dikirim dan tampilkan loading state.
    const reportForm = document.getElementById('report-form');
    const submitButton = document.getElementById('submit-report');
    const submitText = document.getElementById('submit-text');
    const submitLoading = document.getElementById('submit-loading');

    reportForm.addEventListener('submit', function (event) {
        const confirmed = window.confirm(
            'Apakah Anda yakin ingin mengirim laporan ini? Pastikan informasi dan bukti foto sudah benar.'
        );

        if (!confirmed) {
            event.preventDefault();
            return;
        }

        submitButton.disabled = true;
        submitText.classList.add('hidden');
        submitLoading.classList.remove('hidden');
    });
    // Counter karakter deskripsi laporan.
    const descriptionInput = document.getElementById('description');
    const descriptionCount = document.getElementById('description-count');

    function updateDescriptionCount() {
        const currentLength = descriptionInput.value.length;

        descriptionCount.textContent =
            currentLength + '/5000 karakter';

        if (currentLength >= 5000) {
            descriptionCount.classList.add('text-red-700');
            descriptionCount.classList.remove('text-slate-600');
        } else {
            descriptionCount.classList.remove('text-red-700');
            descriptionCount.classList.add('text-slate-600');
        }
    }

    // Perbarui counter ketika pengguna mengetik.
    descriptionInput.addEventListener('input', updateDescriptionCount);

    // Tampilkan jumlah karakter awal, termasuk old input.
    updateDescriptionCount();

    let previewUrls = [];

    evidenceInput.addEventListener('change', function () {
        // Bersihkan preview sebelumnya.
        previewUrls.forEach(function (url) {
            URL.revokeObjectURL(url);
        });

        previewUrls = [];
        evidencePreview.innerHTML = '';
        evidenceError.innerHTML = '';

        const files = Array.from(this.files);

        // Perbarui counter jumlah foto.
        evidenceCount.textContent = files.length + ' dari 5 foto dipilih';

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        const maxSize = 2 * 1024 * 1024;
        const errors = [];

        if (files.length > 5) {
            errors.push('Maksimal hanya 5 foto yang dapat diunggah.');
        }

        files.forEach(function (file) {
            if (!allowedTypes.includes(file.type)) {
                errors.push(file.name + ': Format file tidak didukung.');
            }

            if (file.size > maxSize) {
                errors.push(file.name + ': Ukuran file melebihi 2 MB.');
            }
        });

        // Tampilkan pesan jika terdapat kesalahan.
        if (errors.length > 0) {
            evidenceError.innerHTML = errors
                .map(function (message) {
                    const item = document.createElement('div');
                    item.textContent = message;
                    return item;
                })
                .map(function (item) {
                    return item.outerHTML;
                })
                .join('');

            this.value = '';
            evidenceCount.textContent = '0 dari 5 foto dipilih';
            return;
        }

        // Tampilkan preview foto jika semua valid.
        files.forEach(function (file) {
            const previewUrl = URL.createObjectURL(file);
            previewUrls.push(previewUrl);

            const column = document.createElement('div');
            column.className = 'min-w-0';

            const card = document.createElement('div');
            card.className = 'overflow-hidden rounded-lg border border-slate-200 bg-white';

            const image = document.createElement('img');
            image.src = previewUrl;
            image.alt = 'Preview ' + file.name;
            image.className = 'aspect-video w-full object-cover';

            const cardBody = document.createElement('div');
            cardBody.className = 'p-2';

            const fileName = document.createElement('p');
            fileName.className = 'break-words text-xs text-slate-600';
            fileName.textContent = file.name;

            cardBody.appendChild(fileName);
            card.appendChild(image);
            card.appendChild(cardBody);
            column.appendChild(card);

            evidencePreview.appendChild(column);
        });
    });

    const oldAccessibilityId = @json(old('facility_accessibility_id'));

    const endpointTemplate = facilitySelect.dataset.accessibilityEndpoint;

    let accessibilityRequest;

    async function loadAccessibilities(facilityId) {
        accessibilityRequest?.abort();
        accessibilitySelect.innerHTML = '';
        accessibilitySelect.disabled = true;

        if (!facilityId) {
            accessibilitySelect.innerHTML =
                '<option value="">Pilih fasilitas terlebih dahulu</option>';
            return;
        }

        accessibilitySelect.innerHTML =
            '<option value="">Memuat item aksesibilitas...</option>';

        const request = new AbortController();
        accessibilityRequest = request;

        try {
            const endpoint = endpointTemplate.replace(
                '__FACILITY_ID__',
                facilityId
            );

            const response = await fetch(endpoint, { signal: request.signal });

            if (!response.ok) {
                throw new Error('Gagal mengambil data');
            }

            const items = await response.json();

            if (request.signal.aborted) {
                return;
            }

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
            if (request.signal.aborted) {
                return;
            }

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

