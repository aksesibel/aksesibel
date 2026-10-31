<x-layout :title="$period->exists ? 'Edit Periode Pelaporan' : 'Tambah Periode Pelaporan'">
    <form method="POST" action="{{ $period->exists ? route('manage.reporting-periods.update', $period) : route('manage.reporting-periods.store') }}" class="panel">
        @csrf
        @if ($period->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="md:col-span-2">
                <label for="period_name">Nama periode <span aria-hidden="true">*</span></label>
                <input class="field" id="period_name" name="period_name" value="{{ old('period_name', $period->period_name) }}" maxlength="255" required autofocus
                    @error('period_name') aria-invalid="true" aria-describedby="period-name-error" @enderror>
                @error('period_name') <p id="period-name-error" class="text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="start_date">Tanggal mulai <span aria-hidden="true">*</span></label>
                <input class="field" type="date" id="start_date" name="start_date" value="{{ old('start_date', $period->start_date?->format('Y-m-d')) }}" required
                    @error('start_date') aria-invalid="true" aria-describedby="period-start-error" @enderror>
                @error('start_date') <p id="period-start-error" class="text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="end_date">Tanggal selesai <span aria-hidden="true">*</span></label>
                <input class="field" type="date" id="end_date" name="end_date" value="{{ old('end_date', $period->end_date?->format('Y-m-d')) }}" required
                    @error('end_date') aria-invalid="true" aria-describedby="period-end-error" @enderror>
                @error('end_date') <p id="period-end-error" class="text-red-700">{{ $message }}</p> @enderror
            </div>
        </div>
        <p>Perubahan tanggal tidak memindahkan laporan yang sudah terhubung. Filter periode menggunakan hubungan periode pada laporan.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <button class="button" type="submit">Simpan periode</button>
            <a class="button-secondary" href="{{ $period->exists ? route('manage.reporting-periods.show', $period) : route('manage.reporting-periods.index') }}">Batal</a>
        </div>
    </form>
</x-layout>

