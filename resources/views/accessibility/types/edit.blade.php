@extends('accessibility.layout')
@section('title', 'Ubah Jenis Aksesibilitas')
@section('content')
<div class="kartu">
    <h1>Ubah Jenis Aksesibilitas</h1>
    <form method="POST" action="{{ route('accessibility-types.update', $accessibilityType) }}">
        @csrf
        @method('PUT')
        @include('accessibility.types._form')
        <p>
            <button class="btn" type="submit">Simpan perubahan</button>
            <a class="btn btn-garis" href="{{ route('accessibility-types.index') }}">Batal</a>
        </p>
    </form>
</div>
@endsection