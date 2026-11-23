<x-layout title="Akun saya">
    <section class="panel space-y-3">
        <h2 class="text-lg font-semibold">{{ $record->role->role_name }}</h2>
        @can('manage-supporting-data')
            <p>Gunakan navigasi untuk mengelola pengguna, kategori, periode, dan statistik.</p>
            <a class="button" href="{{ route('manage.statistics') }}">Buka rekap dan statistik</a>
            <a class="button-secondary" href="{{ route('accessibility-types.index') }}">Kelola aksesibilitas</a>
            <a class="button-secondary" href="{{ route('follow-up-assignments.ready-reports') }}">Buka penugasan tindak lanjut</a>
        @else
            @if ($record->role->role_name === \App\Models\Role::ADMIN)
                <a class="button" href="{{ route('verifikasi.antrean') }}">Buka antrean verifikasi</a>
                <a class="button-secondary" href="{{ route('verifikasi.status') }}">Kelola status verifikasi</a>
                <a class="button-secondary" href="{{ route('admin.facilities.index') }}">Kelola fasilitas</a>
                <a class="button-secondary" href="{{ route('follow-up-assignments.ready-reports') }}">Buka penugasan tindak lanjut</a>
            @else
                <a class="button" href="{{ route('reports.index') }}">Buka laporan saya</a>
                <a class="button-secondary" href="{{ route('reports.create') }}">Buat laporan</a>
            @endif
        @endcan
    </section>
    <form class="panel max-w-2xl space-y-5" method="POST" action="{{ route('profile.update') }}">
        @csrf @method('PATCH')
        <h2 class="text-lg font-semibold">Perbarui profil</h2>
        @include('users.identity-fields', ['record' => $record])
        <button class="button" type="submit">Simpan profil</button>
    </form>
</x-layout>
