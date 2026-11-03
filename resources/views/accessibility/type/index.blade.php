@extends('accessibility.layout')

@section('title', 'Jenis Aksesibilitas')

@section('content')
    <div class="card">
        <h1>Jenis Aksesibilitas</h1>

        <table>
            <caption class="sr-only">Daftar jenis sarana aksesibilitas</caption>
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($types as $type)
                    <tr>
                        <td>{{ $type->type_name }}</td>
                        <td>{{ $type->description ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">Belum ada jenis aksesibilitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection