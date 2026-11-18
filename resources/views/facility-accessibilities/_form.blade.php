{{-- Pilih Fasilitas --}}
<div class="field">
    <label for="facility_id">Nama Fasilitas <span class="required">*</span></label>

    <select name="facility_id" id="facility_id" class="control" required>
        <option value="">-- Pilih Fasilitas --</option>

        @foreach ($facilities as $facility)
            <option value="{{ $facility->facility_id }}" {{ old('facility_id', $facilityAccessibility->facility_id ?? '') == $facility->facility_id ? 'selected' : '' }}>
                {{ $facility->name }}
            </option>
        @endforeach
    </select>

    @error('facility_id')
        <small class="field-error">{{ $message }}</small>
    @enderror
</div>

{{-- Pilih Jenis Aksesibilitas --}}
<div class="field">
    <label for="accessibility_type_id">Jenis Aksesibilitas <span class="required">*</span></label>

    <select name="accessibility_type_id" id="accessibility_type_id" class="control" required>
        <option value="">-- Pilih Jenis Aksesibilitas --</option>

        @foreach ($accessibilityTypes as $type)
            <option value="{{ $type->accessibility_type_id }}" {{ old('accessibility_type_id', $facilityAccessibility->accessibility_type_id ?? '') == $type->accessibility_type_id ? 'selected' : '' }}>
                {{ $type->type_name }}
            </option>
        @endforeach
    </select>

    @error('accessibility_type_id')
        <small class="field-error">{{ $message }}</small>
    @enderror
</div>

{{-- Status Ketersediaan --}}
<div class="field">
    <label for="availability_status">Ketersediaan</label>

    <select name="availability_status" id="availability_status" class="control">
        <option value="">-- Pilih Ketersediaan --</option>

        @foreach (\App\Models\FacilityAccessibility::AVAILABILITY_LABELS as $value => $label)
            <option value="{{ $value }}" {{ old('availability_status', $facilityAccessibility->availability_status ?? '') == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>

    @error('availability_status')
        <small class="field-error">{{ $message }}</small>
    @enderror
</div>

{{-- Kondisi --}}
<div class="field">
    <label for="condition_status">Kondisi</label>

    <select name="condition_status" id="condition_status" class="control">
        <option value="">-- Pilih Kondisi --</option>

        @foreach (\App\Models\FacilityAccessibility::CONDITION_LABELS as $value => $label)
            <option value="{{ $value }}" {{ old('condition_status', $facilityAccessibility->condition_status ?? '') == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>

    @error('condition_status')
        <small class="field-error">{{ $message }}</small>
    @enderror
</div>

{{-- Lokasi Sarana --}}
<div class="field">
    <label for="location_description">Lokasi Sarana</label>

    <textarea name="location_description" id="location_description" class="control" rows="3"
        placeholder="Contoh: Lantai 1, dekat pintu masuk utama.">{{ old('location_description', $facilityAccessibility->location_description ?? '') }}</textarea>

    @error('location_description')
        <small class="field-error">{{ $message }}</small>
    @enderror
</div>

{{-- Foto Dokumentasi --}}
<div class="field">
    <label for="photo">Foto Dokumentasi</label>

    <input type="file" name="photo" id="photo" class="control" accept=".jpg,.jpeg,.png,.webp">

    <small class="field-hint">
        Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
    </small>

    @error('photo')
        <small class="field-error">{{ $message }}</small>
    @enderror

    @if (!empty($facilityAccessibility?->photo_path))
        <div class="current-photo">
            <p>Foto saat ini:</p>
            <img src="{{ asset('storage/' . $facilityAccessibility->photo_path) }}" alt="Foto dokumentasi aksesibilitas"
                style="max-width: 200px; border-radius: 8px;">
        </div>
    @endif
</div>

{{-- Catatan --}}
<div class="field">
    <label for="notes">Catatan Tambahan</label>

    <textarea name="notes" id="notes" class="control" rows="3"
        placeholder="Tambahkan informasi lain jika diperlukan.">{{ old('notes', $facilityAccessibility->notes ?? '') }}</textarea>

    @error('notes')
        <small class="field-error">{{ $message }}</small>
    @enderror
</div>

{{-- Tombol Aksi --}}
<div class="form-actions">
    <button type="submit" class="btn btn-primary">
        {{ $submitLabel ?? 'Simpan' }}
    </button>

    <a href="{{ route('facility-accessibilities.index') }}" class="btn btn-secondary">
        Batal
    </a>
</div>