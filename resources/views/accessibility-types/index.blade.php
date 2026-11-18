@extends('layout.pengelola')

@section('title', 'Kelola Jenis Aksesibilitas')
@section('breadcrumb', 'Aksesibel / Data Aksesibilitas / Jenis Aksesibilitas')

@section('content')
    <div class="page-header">
        <div>
            <h1>Kelola Jenis Aksesibilitas</h1>
            <p>Kelola data master jenis aksesibilitas.</p>
        </div>

        <a href="{{ route('accessibility-types.create') }}" class="btn btn-primary">
            + Tambah Jenis
        </a>
    </div>

    {{-- Notifikasi --}}
    @if ($errors->has('hapus'))
        <div class="alert alert-danger">
            {{ $errors->first('hapus') }}
        </div>
    @endif

    {{-- Pencarian --}}
    <div class="card">
        <form action="{{ route('accessibility-types.index') }}" method="GET">
            <div class="field">
                <label for="q">Cari Jenis Aksesibilitas</label>

                <div class="row">
                    <input type="text" name="q" id="q" class="control" placeholder="Masukkan nama jenis aksesibilitas..."
                        value="{{ $q }}">

                    <button type="submit" class="btn btn-primary">
                        Cari
                    </button>

                    <a href="{{ route('accessibility-types.index') }}" class="btn">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Data --}}
    <div class="card">
        <h3>Daftar Jenis Aksesibilitas</h3>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Jenis</th>
                        <th>Deskripsi</th>
                        <th>Digunakan pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($types as $type)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $type->type_name }}</td>

                            <td class="description-cell">
                                {{ $type->description ?: '-' }}
                            </td>

                            <td>
                                {{ $type->facility_accessibilities_count }}
                                fasilitas
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('accessibility-types.edit', $type->accessibility_type_id) }}"
                                        class="btn btn-sm">
                                        Edit
                                    </a>

                                    <form action="{{ route('accessibility-types.destroy', $type->accessibility_type_id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus jenis aksesibilitas ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;">
                                Belum ada data jenis aksesibilitas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection