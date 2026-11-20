@extends('layout.pengelola')

@section('title', 'Kelola Aksesibilitas Fasilitas')
@section('breadcrumb', 'Aksesibel / Data Aksesibilitas / Fasilitas Aksesibilitas')

@section('content')

    <div class="page-header">
        <div>
            <h1>Kelola Aksesibilitas Fasilitas</h1>
            <p>Kelola data sarana aksesibilitas pada setiap fasilitas.</p>
        </div>

        <a href="{{ route('facility-accessibilities.create') }}" class="btn btn-primary">
            + Tambah Aksesibilitas
        </a>
    </div>

    {{-- Pencarian --}}
    <div class="card">
        <form action="{{ route('facility-accessibilities.index') }}" method="GET">
            <div class="field">
                <label for="q">Cari Aksesibilitas</label>

                <div class="row">
                    <input type="text" name="q" id="q" class="control"
                        placeholder="Cari nama fasilitas atau jenis aksesibilitas..." value="{{ request('q') }}">

                    <button type="submit" class="btn btn-primary">
                        Cari
                    </button>

                    <a href="{{ route('facility-accessibilities.index') }}" class="btn">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Data --}}
    <div class="card">
        <h3>Daftar Aksesibilitas Fasilitas</h3>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Fasilitas</th>
                        <th>Jenis Aksesibilitas</th>
                        <th>Ketersediaan</th>
                        <th>Kondisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($facilityAccessibilities as $accessibility)
                        <tr>
                            <td>{{ $facilityAccessibilities->firstItem() + $loop->index }}</td>

                            <td>
                                {{ $accessibility->facility->name ?? '-' }}
                            </td>

                            <td>
                                {{ $accessibility->accessibilityType->type_name ?? '-' }}
                            </td>

                            <td>
                                {{ \App\Models\FacilityAccessibility::AVAILABILITY_LABELS[$accessibility->availability_status] ?? '-' }}
                            </td>

                            <td>
                                {{ \App\Models\FacilityAccessibility::CONDITION_LABELS[$accessibility->condition_status] ?? '-' }}
                            </td>

                            <td>
                                <a href="{{ route('facility-accessibilities.show', $accessibility->facility_accessibility_id) }}"
                                    class="btn btn-sm">
                                    Detail
                                </a>

                                <a href="{{ route('facility-accessibilities.edit', $accessibility->facility_accessibility_id) }}"
                                    class="btn btn-sm">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center;">
                                Belum ada data aksesibilitas fasilitas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $facilityAccessibilities->links() }}
        </div>
    </div>

@endsection