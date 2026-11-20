<nav class="module-tabs" aria-label="Data pendukung">
    @foreach (['users' => ['Pengguna', 'users'], 'facility-categories' => ['Kategori fasilitas', 'building'], 'issue-categories' => ['Kategori kendala', 'database'], 'reporting-periods' => ['Periode laporan', 'calendar']] as $key => [$label, $icon])
        @php($active = request()->routeIs('manage.'.$key.'*'))
        <a class="module-tab {{ $active ? 'is-active' : '' }}" href="{{ route('manage.'.$key.'.index') }}" @if($active) aria-current="page" @endif><x-icon :name="$icon" /><span>{{ $label }}</span></a>
    @endforeach
</nav>
