@props(['role', 'mobile' => false])

@php
    $links = match ($role) {
        \App\Models\Role::PUBLIC => [
            ['Laporan saya', 'reports.index', 'reports.show', 'grid'],
            ['Buat laporan', 'reports.create', 'reports.create', 'report'],
            ['Cari fasilitas', 'facilities.index', 'facilities.*', 'search'],
        ],
        \App\Models\Role::ADMIN => [
            ['Antrean verifikasi', 'verifikasi.antrean', 'verifikasi.antrean', 'report'],
            ['Riwayat verifikasi', 'verifikasi.riwayat', 'verifikasi.riwayat', 'calendar'],
            ['Kelola status', 'verifikasi.status', 'verifikasi.status', 'shield'],
            ['Kelola fasilitas', 'admin.facilities.index', 'admin.facilities.*', 'building'],
            ['Kategori fasilitas', 'admin.categories.index', 'admin.categories.*', 'database'],
            ['Penugasan', 'follow-up-assignments.ready-reports', 'follow-up-assignments.*', 'tool'],
        ],
        \App\Models\Role::FOLLOW_UP_OFFICER => [
            ['Tugas saya', 'follow-up-assignments.index', 'follow-up-assignments.*', 'report'],
            ['Cari fasilitas', 'facilities.index', 'facilities.*', 'search'],
        ],
        default => [
            ['Pengguna dan Hak Akses', 'manage.users.index', 'manage.users.*', 'users'],
            ['Kategori Fasilitas', 'manage.facility-categories.index', 'manage.facility-categories.*', 'building'],
            ['Kategori Kendala', 'manage.issue-categories.index', 'manage.issue-categories.*', 'database'],
            ['Periode Pelaporan', 'manage.reporting-periods.index', 'manage.reporting-periods.*', 'calendar'],
            ['Rekap dan Statistik', 'manage.statistics', 'manage.statistics', 'chart'],
            ['Data Aksesibilitas', 'accessibility-types.index', ['accessibility-types.*', 'facility-accessibilities.*'], 'grid'],
            ['Penugasan', 'follow-up-assignments.ready-reports', 'follow-up-assignments.*', 'tool'],
        ],
    };
@endphp

@if ($mobile)
    @php
        $mobileLinks = $role === \App\Models\Role::FACILITY_MANAGER
            ? [$links[4], $links[5], $links[6]]
            : array_slice($links, 0, 3);
    @endphp
    <nav class="mobile-tabbar lg:hidden" aria-label="Navigasi cepat">
        @foreach ($mobileLinks as [$label, $route, $pattern, $icon])
            <a href="{{ route($route) }}" @if(request()->routeIs($pattern)) aria-current="page" @endif>
                <x-icon :name="$icon" :size="18" />
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>
@else
    <aside class="workspace-sidebar" id="workspace-navigation" aria-label="Ruang kerja {{ $role }}">
        <div class="sidebar-brand-row">
            <a class="brand sidebar-brand" href="{{ route('home') }}">
                <span class="brand-mark" aria-hidden="true"><img src="{{ asset('images/aksesibel/logo-ruang-kerja.svg') }}" alt=""></span>
                Aksesibel
            </a>
            <button class="icon-button sidebar-close" type="button" data-menu-close aria-label="Tutup navigasi"><x-icon name="close" /></button>
        </div>
        <nav aria-label="{{ $role === \App\Models\Role::FACILITY_MANAGER ? 'Modul pengelola' : 'Modul ruang kerja' }}" class="sidebar-nav">
            <p class="sidebar-label">Ruang kerja</p>
            @foreach ($links as [$label, $route, $pattern, $icon])
                @php($active = request()->routeIs($pattern))
                <a href="{{ route($route) }}" class="sidebar-link {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif>
                    <x-icon :name="$icon" :size="18" />
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
        <div class="sidebar-access">
            <x-icon name="shield" :size="22" />
            <p>Akses sesuai kewenangan</p>
            <span>Anda masuk sebagai {{ $role }}.</span>
        </div>
        <div class="sidebar-footer">
            @unless($role === \App\Models\Role::FOLLOW_UP_OFFICER)
                <a href="{{ route('account') }}">Akun saya</a><span aria-hidden="true">·</span>
            @endunless
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Keluar <x-icon name="logout" :size="15" /></button>
            </form>
        </div>
    </aside>
@endif
