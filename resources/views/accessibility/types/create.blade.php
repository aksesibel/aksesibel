@extends('accessibility.layout')
@section('title', 'Tambah Jenis Aksesibilitas')
@section('content')
<div class="kartu">
    <h1>Tambah Jenis Aksesibilitas</h1>
    <form method="POST" action="{{ route('accessibility-types.store') }}">
        @csrf
        @include('accessibility.types._form')
        <p>
            <button class="btn" type="submit">Simpan</button>
            <a class="btn btn-garis" href="{{ route('accessibility-types.index') }}">Batal</a>
        </p>
    </form>
</div>
@endsection