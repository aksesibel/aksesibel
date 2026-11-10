
@extends('layouts.masyarakat')

@section('title', 'Tambah Kategori')
@section('breadcrumb', 'Kelola Kategori Kendala / Tambah')

@section('content')
    <div class="mb-4">
        <a href="{{ route('issue-categories.index') }}"
           class="text-decoration-none text-primary">
            ← Kembali ke Daftar Kategori
        </a>

        <h3 class="fw-bold mt-3 mb-1">Tambah Kategori Kendala</h3>
        <p class="text-muted mb-0">
            Tambahkan kategori baru untuk mengelompokkan laporan kendala aksesibilitas.
        </p>
    </div>

    <div class="content-card" style="max-width: 750px;">
        <h5 class="fw-bold mb-1">Informasi Kategori</h5>
        <p class="text-muted mb-4">
            Lengkapi informasi kategori kendala berikut.
        </p>

        <form action="{{ route('issue-categories.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="category_name" class="form-label fw-semibold">
                    Nama Kategori <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    name="category_name"
                    id="category_name"
                    class="form-control @error('category_name') is-invalid @enderror"
                    value="{{ old('category_name') }}"
                    placeholder="Contoh: Fasilitas rusak"
                    maxlength="255"
                    required
                >

                @error('category_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <small class="text-muted">
                    Gunakan nama kategori yang singkat dan mudah dipahami.
                </small>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label fw-semibold">
                    Deskripsi
                </label>

                <textarea
                    name="description"
                    id="description"
                    rows="4"
                    class="form-control @error('description') is-invalid @enderror"
                    placeholder="Jelaskan jenis kendala yang termasuk dalam kategori ini..."
                >{{ old('description') }}</textarea>

                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('issue-categories.index') }}"
                   class="btn btn-outline-secondary">
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
@endsection
