<nav class="accessibility-tabs" aria-label="Navigasi Data Aksesibilitas">

    <a
        href="{{ route('accessibility-types.index') }}"
        class="accessibility-tab {{ request()->routeIs('accessibility-types.*') ? 'active' : '' }}"
        @if(request()->routeIs('accessibility-types.*'))
            aria-current="page"
        @endif
    >
        Jenis Aksesibilitas
    </a>

    <a
        href="{{ route('facility-accessibilities.index') }}"
        class="accessibility-tab {{ request()->routeIs('facility-accessibilities.*') ? 'active' : '' }}"
        @if(request()->routeIs('facility-accessibilities.*'))
            aria-current="page"
        @endif
    >
        Fasilitas Aksesibilitas
    </a>

</nav>