
@extends('layout.pengelola')

@section('title', 'Tambah Aksesibilitas Fasilitas')
@section('breadcrumb', 'Aksesibel / Data Aksesibilitas / Tambah Data')

@section('content')

    <div class="page-header">
        <div>
            <h1>Tambah Aksesibilitas Fasilitas</h1>
            <p>Tambahkan data sarana aksesibilitas pada fasilitas.</p>
        </div>
    </div>

    <div class="card form-card">
        <form
            action="{{ route('facility-accessibilities.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf

            @include('facility-accessibilities._form', [
                'submitLabel' => 'Simpan Data'
            ])
        </form>
    </div>

@endsection
