@extends('layout.pengelola')

@section('title', 'Tambah Jenis Aksesibilitas')
@section('breadcrumb', 'Aksesibel / Tambah Jenis')

@section('content')
    <div class="page-header">
        <div>
            <h1>Tambah Jenis Aksesibilitas</h1>
            <p>Tambahkan jenis aksesibilitas baru ke dalam sistem.</p>
        </div>
    </div>

    <div class="card form-card">
        <form action="{{ route('accessibility-types.store') }}" method="POST">
            @csrf

            @include('accessibility-types._form', [
                'submitLabel' => 'Simpan Data'
            ])
        </form>
    </div>
@endsection