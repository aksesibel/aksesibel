@extends('accessibility.layout')
@section('title', 'Jenis Aksesibilitas')
@section('content')
<div class="kartu">
    <h1>Jenis Aksesibilitas</h1>
    <a class="btn" href="{{ route('accessibility-types.create') }}">Tambah jenis</a>

    @error('hapus') <p role="alert" class="galat">{{ $message }}</p> @enderror

    <table>
        <caption class="sr-only">Daftar jenis sarana aksesibilitas</caption>
        <thead><tr><th scope="col">Nama</th><th scope="col">Deskripsi</th><th scope="col">Tindakan</th></tr></thead>
        <tbody>
        @forelse ($types as $type)
            <tr>
                <td>{{ $type->type_name }}</td>
                <td>{{ $type->description ?? '-' }}</td>
                <td>
                    <a href="{{ route('accessibility-types.edit', $type) }}">Edit</a>
                    <form method="POST" action="{{ route('accessibility-types.destroy', $type) }}" style="display:inline"
                          onsubmit="return confirm('Hapus jenis ini?')">
                        @csrf @method('DELETE')
                        <button type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3">Belum ada jenis aksesibilitas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection