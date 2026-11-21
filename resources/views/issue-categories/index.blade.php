
@extends('layouts.masyarakat')

@section('title', 'Kategori Kendala')
@section('breadcrumb', 'Kelola Kategori Kendala')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Kategori Kendala</h3>
            <p class="text-muted mb-0">
                Kelola kategori kendala aksesibilitas yang dapat digunakan masyarakat.
            </p>
        </div>

        <a href="{{ route('issue-categories.create') }}" class="btn btn-primary">
            + Tambah Kategori
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1">Daftar Kategori</h5>
                <small class="text-muted">
                    Total {{ $categories->total() }} kategori kendala
                </small>
            </div>
        </div>

        <form action="{{ route('issue-categories.index') }}"
            method="GET"
            class="mb-4">

            <div class="row g-2">
                <div class="col-md-6">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari nama kategori kendala..."
                        value="{{ $search }}"
                    >
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="" {{ $status === '' ? 'selected' : '' }}>
                            Semua Status
                        </option>

                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>
                            Aktif
                        </option>

                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>
                            Nonaktif
                        </option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        Filter
                    </button>

                    @if($search !== '' || $status !== '')
                        <a href="{{ route('issue-categories.index') }}"
                        class="btn btn-outline-secondary">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">No.</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($categories as $index => $category)
                        <tr>
                            <td>
                                {{ $categories->firstItem() + $index }}
                            </td>

                            <td class="fw-semibold">
                                {{ $category->category_name }}
                            </td>

                            <td class="text-muted">
                                {{ $category->description ?: 'Belum ada deskripsi' }}
                            </td>

                            <td>
                                @if ($category->is_active)
                                    <span class="badge rounded-pill text-bg-success">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge rounded-pill text-bg-secondary">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('issue-categories.edit', $category->issue_category_id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>

                                    <form action="{{ route('issue-categories.toggle-status', $category->issue_category_id) }}"
                                            method="POST"
                                            onsubmit="return confirm(@js(
                                                'Apakah Anda yakin ingin ' .
                                                ($category->is_active ? 'menonaktifkan' : 'mengaktifkan') .
                                                ' kategori ' . $category->category_name . '?'
                                            ))">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="btn btn-sm {{ $category->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                            {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                @if($search !== '' || $status !== '')
                                    <div class="fw-semibold mb-1">
                                        Kategori tidak ditemukan.
                                    </div>

                                    <div>
                                        Tidak ada kategori yang sesuai dengan pencarian atau filter status.
                                    </div>

                                    <a href="{{ route('issue-categories.index') }}"
                                    class="btn btn-sm btn-outline-primary mt-3">
                                        Reset Pencarian dan Filter
                                    </a>
                                @else
                                    <div class="fw-semibold mb-1">
                                        Belum ada kategori kendala.
                                    </div>

                                    <div>
                                        Silakan tambahkan kategori baru.
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $categories->links() }}
        </div>
    </div>
@endsection
