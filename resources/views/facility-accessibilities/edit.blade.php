@extends('layout.pengelola')

@section('title', 'Edit Aksesibilitas Fasilitas')
@section('breadcrumb', 'Aksesibel / Data Aksesibilitas / Edit Data')

@section('content')

    <div class="page-header">
        <div>
            <h1>Edit Aksesibilitas Fasilitas</h1>
            <p>Perbarui informasi sarana aksesibilitas pada fasilitas.</p>
        </div>
    </div>

    <div class="card form-card">
        <form
            action="{{ route('facility-accessibilities.update', $facilityAccessibility->facility_accessibility_id) }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')

            @include('facility-accessibilities._form', [
                'submitLabel' => 'Simpan Perubahan'
            ])
        </form>
    </div>

@endsection
