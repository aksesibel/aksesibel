<x-layout title="Akses fasilitas publik yang lebih baik">
    <section class="panel max-w-3xl space-y-4">
        <h2 class="text-xl font-semibold">Bersama membangun lingkungan yang inklusif</h2>
        <p>Kelola data pendukung dan pantau rekap laporan aksesibilitas fasilitas publik.</p>
        <div class="flex flex-wrap gap-3">
            @auth
                <a href="{{ route('account') }}" class="button">Buka akun saya</a>
                @can('manage-supporting-data')<a href="{{ route('manage.statistics') }}" class="button-secondary">Rekap dan Statistik</a>@endcan
            @else
                <a href="{{ route('login') }}" class="button">Masuk ke akun</a><a href="{{ route('register') }}" class="button-secondary">Daftar sebagai masyarakat</a>
            @endauth
        </div>
    </section>
</x-layout>
