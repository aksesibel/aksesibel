
<div class="field">
    <label for="type_name">Nama Jenis Aksesibilitas</label>

    <input
        type="text"
        name="type_name"
        id="type_name"
        class="control"
        value="{{ old('type_name', $accessibilityType->type_name ?? '') }}"
        placeholder="Contoh: Ramp"
        required
        maxlength="255"
    >

    @error('type_name')
        <small class="field-error">{{ $message }}</small>
    @enderror
</div>

<div class="field">
    <label for="description">Deskripsi</label>

    <textarea
        name="description"
        id="description"
        class="control"
        rows="4"
        placeholder="Masukkan deskripsi jenis aksesibilitas..."
    >{{ old('description', $accessibilityType->description ?? '') }}</textarea>

    @error('description')
        <small class="field-error">{{ $message }}</small>
    @enderror
</div>

<div class="form-actions">
    <button type="submit" class="btn">
        {{ $submitLabel ?? 'Simpan' }}
    </button>

    <a href="{{ route('accessibility-types.index') }}" class="btn btn-secondary">
        Batal
    </a>
</div>
