<x-layout title="Akun saya">
    <section class="panel space-y-3">
        <h2 class="text-lg font-semibold">{{ $record->role->role_name }}</h2>
        @can('manage-supporting-data')
            <p>Gunakan navigasi untuk mengelola pengguna, kategori, periode, dan statistik.</p>
            <a class="button" href="{{ route('manage.statistics') }}">Buka rekap dan statistik</a>
        @else
            <p>{{ $record->role->role_name === \App\Models\Role::ADMIN ? 'Peran Admin digunakan untuk verifikasi laporan.' : 'Peran Masyarakat digunakan untuk pelaporan pribadi.' }}</p>
            <p class="text-sm text-slate-600">Integrasi modul {{ $record->role->role_name === \App\Models\Role::ADMIN ? 'verifikasi laporan' : 'pelaporan pribadi' }} menunggu endpoint dan antarmuka dari pemilik modul. Modul tersebut belum tersedia di repository ini.</p>
        @endcan
    </section>
    <form class="panel max-w-2xl space-y-5" method="POST" action="{{ route('profile.update') }}">
        @csrf @method('PATCH')
        <h2 class="text-lg font-semibold">Perbarui profil</h2>
        @include('users.identity-fields', ['record' => $record])
        <button class="button" type="submit">Simpan profil</button>
    </form>
</x-layout>
