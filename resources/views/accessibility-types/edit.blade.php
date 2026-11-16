
@extends('layout.accessibility-types')

@section('title', 'Edit Jenis Aksesibilitas')
@section('breadcrumb', 'Aksesibel / Edit Jenis')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit Jenis Aksesibilitas</h1>
            <p>Ubah informasi jenis aksesibilitas.</p>
        </div>
    </div>

    <div class="card form-card">
        <form
            action="{{ route('accessibility-types.update', $accessibilityType->accessibility_type_id) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            @include('accessibility-types._form', [
                'accessibilityType' => $accessibilityType,
                'submitLabel' => 'Simpan Perubahan'
            ])
        </form>
    </div>
@endsection
