<x-layout :title="($category->exists ? 'Edit ' : 'Tambah ').$title">
    <form method="POST" action="{{ $category->exists ? route($routePrefix.'.update', $category) : route($routePrefix.'.store') }}" class="panel"
        @if ($category->exists) data-confirm="Simpan perubahan kategori? Kategori nonaktif tidak tersedia untuk pilihan baru; histori tetap tersimpan." @endif>
        @csrf
        @if ($category->exists) @method('PUT') @endif
        <div class="form-grid">
            <div>
                <label for="category_name">Nama kategori <span aria-hidden="true">*</span></label>
                <input class="field" id="category_name" name="category_name" value="{{ old('category_name', $category->category_name) }}" maxlength="255" required autofocus
                    @error('category_name') aria-invalid="true" aria-describedby="category-name-error" @enderror>
                @error('category_name') <p id="category-name-error" class="text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="is_active">Status <span aria-hidden="true">*</span></label>
                <select class="field" id="is_active" name="is_active" required @error('is_active') aria-invalid="true" aria-describedby="category-status-error" @enderror>
                    <option value="1" @selected((string) old('is_active', $category->exists ? (int) $category->is_active : 1) === '1')>Aktif</option>
                    <option value="0" @selected((string) old('is_active', $category->exists ? (int) $category->is_active : 1) === '0')>Nonaktif</option>
                </select>
                @error('is_active') <p id="category-status-error" class="text-red-700">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label for="description">Deskripsi (opsional)</label>
                <textarea class="field" id="description" name="description" rows="4" @error('description') aria-invalid="true" aria-describedby="category-description-error" @enderror>{{ old('description', $category->description) }}</textarea>
                @error('description') <p id="category-description-error" class="text-red-700">{{ $message }}</p> @enderror
            </div>
        </div>
        <p>Kategori aktif tersedia untuk pilihan baru. Penonaktifan tidak mengubah fasilitas atau laporan yang sudah terhubung.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <button class="button" type="submit">Simpan kategori</button>
            <a class="button-secondary" href="{{ $category->exists ? route($routePrefix.'.show', $category) : route($routePrefix.'.index') }}">Batal</a>
        </div>
    </form>
</x-layout>

