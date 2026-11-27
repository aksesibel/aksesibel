<aside class="workspace-sidebar" id="workspace-navigation" aria-label="Ruang kerja pengelola">
    <div class="sidebar-brand-row">
        <a class="brand sidebar-brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">♿︎</span>Aksesibel</a>
        <button class="icon-button sidebar-close" type="button" data-menu-close aria-label="Tutup navigasi"><x-icon name="close" /></button>
    </div>
    <nav aria-label="Modul pengelola" class="sidebar-nav">
        <p class="sidebar-label">Ruang kerja</p>
        <p class="sidebar-section"><x-icon name="grid" />Kelola data</p>
        @foreach (['users' => ['Pengguna dan Hak Akses', 'users'], 'facility-categories' => ['Kategori Fasilitas', 'building'], 'issue-categories' => ['Kategori Kendala', 'database'], 'reporting-periods' => ['Periode Pelaporan', 'calendar'], 'statistics' => ['Rekap dan Statistik', 'chart']] as $key => [$label, $icon])
            @php($active = request()->routeIs('manage.'.$key.'*'))
            <a href="{{ route('manage.'.$key.($key === 'statistics' ? '' : '.index')) }}" class="sidebar-link {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif><x-icon :name="$icon" /><span>{{ $label }}</span></a>
        @endforeach
    </nav>
    <div class="sidebar-access"><x-icon name="shield" :size="23" /><p>Akses sesuai kewenangan</p><span>Anda masuk sebagai Pengelola Fasilitas.</span></div>
    <div class="sidebar-footer">
        <a href="{{ route('account') }}">Akun saya</a><span aria-hidden="true">·</span>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar <x-icon name="logout" :size="15" /></button></form>
    </div>
</aside>
