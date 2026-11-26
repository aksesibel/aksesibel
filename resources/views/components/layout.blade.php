@props(['title' => 'Aksesibel', 'subtitle' => null, 'workspace' => null, 'breadcrumb' => null, 'showHeading' => true, 'fullWidth' => false, 'module' => null])

@php
    $role = auth()->user()?->role?->role_name;
    $workspace = in_array($role, [...\App\Models\Role::PROPOSAL_ROLES, \App\Models\Role::FOLLOW_UP_OFFICER], true)
        && ($workspace ?? request()->routeIs('manage.*', 'admin.*', 'account'));
    $accountUrl = route($role === \App\Models\Role::FOLLOW_UP_OFFICER ? 'follow-up-assignments.index' : 'account');
    $reportUrl = route($role === \App\Models\Role::PUBLIC ? 'reports.create' : 'login');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $metaDesc ?? 'Platform informasi aksesibilitas fasilitas publik di Indonesia.' }}">
    <title>{{ $title }} · Aksesibel</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    {{ $head ?? '' }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body id="top" class="min-h-screen flex flex-col bg-page-bg text-text-main antialiased {{ $workspace ? 'workspace' : 'public-site' }}" x-data @if($workspace) data-workspace @endif>
<a href="#main-content" class="skip-link">Lewati ke konten</a>

@if ($workspace)
    <x-workspace-navigation :role="$role" />
    <button class="sidebar-backdrop" type="button" data-menu-close hidden aria-label="Tutup menu navigasi" tabindex="-1"></button>
    <div class="workspace-content" id="workspace-content">
        <header class="workspace-topbar">
            <div class="topbar-location">
                <button class="icon-button menu-toggle" type="button" data-menu-toggle aria-controls="workspace-navigation" aria-expanded="false" aria-label="Buka navigasi"><x-icon name="menu" /></button>
                <span>Aksesibel <span class="breadcrumb-divider">/</span> <span class="breadcrumb-current">{{ $breadcrumb ?: $title }}</span></span>
            </div>
            <div class="topbar-account">
                <span class="data-label">Data aplikasi</span>
                <a href="{{ $accountUrl }}" class="role-label"><x-icon name="users" :size="16" /><span>{{ $role }}</span></a>
                <a class="account-name" href="{{ $accountUrl }}">{{ auth()->user()->name }}</a>
            </div>
        </header>
        <main id="main-content" class="workspace-main {{ $module ? 'module-'.$module : '' }}" tabindex="-1">
            @if($showHeading)
                <div class="page-heading">
                    <div><h1>{{ $title }}</h1>@if($subtitle)<p>{{ $subtitle }}</p>@endif</div>
                    @isset($actions)<div class="page-actions">{{ $actions }}</div>@endisset
                </div>
            @endif
            @if(request()->routeIs('manage.*') && ! request()->routeIs('manage.statistics'))
                <x-workspace-tabs />
            @endif
            <x-page-feedback />
            {{ $slot }}
        </main>
        <footer class="workspace-footer">Aksesibel · Ruang publik untuk semua</footer>
        <x-workspace-navigation :role="$role" mobile />
    </div>
@else
    <header class="public-header" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
        <div class="public-container flex min-h-16 items-center justify-between gap-3 md:min-h-[88px]">
            <a href="{{ route('home') }}" class="public-brand" aria-label="Aksesibel – Beranda">
                <span class="public-brand-mark" aria-hidden="true"><img src="{{ asset('images/aksesibel/logo-publik.svg') }}" alt=""></span>Aksesibel
            </a>
            <nav aria-label="Navigasi utama" class="hidden items-center gap-[30px] md:flex">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
                <a href="{{ route('facilities.index') }}" @if(request()->routeIs('facilities.*')) aria-current="page" @endif>Cari fasilitas</a>
                <a href="{{ route('home') }}#panduan">Panduan aksesibilitas</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ $accountUrl }}" class="button-secondary hidden sm:inline-flex"><x-icon name="users" :size="16" />{{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="button-secondary">Keluar</button></form>
                @else
                    <span class="data-label"><x-icon name="users" :size="16" /> Pengunjung</span>
                    <a href="{{ route('login') }}" class="button-secondary hidden sm:inline-flex">Masuk / Daftar</a>
                @endauth
                <button type="button" class="icon-button md:hidden" @click="open = !open" :aria-expanded="open" aria-controls="mobile-nav" aria-label="Buka menu navigasi"><x-icon name="menu" /></button>
            </div>
        </div>
        <nav id="mobile-nav" x-show="open" x-cloak class="public-container grid gap-1 border-t border-slate-200 py-3 text-sm md:hidden" aria-label="Menu seluler">
            <a class="mobile-menu-link" href="{{ route('home') }}">Beranda</a>
            <a class="mobile-menu-link" href="{{ route('facilities.index') }}">Cari fasilitas</a>
            <a class="mobile-menu-link" href="{{ route('home') }}#panduan" @click="open = false">Panduan aksesibilitas</a>
            <a class="mobile-menu-link" href="{{ auth()->check() ? $accountUrl : route('login') }}">{{ auth()->check() ? 'Akun saya' : 'Masuk / Daftar' }}</a>
        </nav>
    </header>
    {{-- Toast notifikasi --}}
    @if (session('toast_success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             role="status"
             aria-live="polite"
             class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-[#15803D] text-white px-5 py-3 rounded-xl shadow-lg max-w-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            <span class="text-sm font-medium">{{ session('toast_success') }}</span>
            <button @click="show = false" class="ml-auto p-1 rounded hover:bg-white/20 transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Tutup notifikasi">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if (session('toast_error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             role="alert"
             aria-live="assertive"
             class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-[#B91C1C] text-white px-5 py-3 rounded-xl shadow-lg max-w-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span class="text-sm font-medium">{{ session('toast_error') }}</span>
            <button @click="show = false" class="ml-auto p-1 rounded hover:bg-white/20 transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Tutup notifikasi">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    <main id="main-content" class="{{ $fullWidth ? 'flex-1' : 'public-container flex-1 space-y-6 py-6' }} {{ $module ? 'module-'.$module : '' }}" tabindex="-1">
        <x-page-feedback />
        {{ $slot }}
    </main>
    <x-public-footer :report-url="$reportUrl" :account-url="$accountUrl" />
    <nav class="mobile-tabbar md:hidden" aria-label="Navigasi cepat">
        <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif><x-icon name="grid" :size="18" /><span>Beranda</span></a>
        <a href="{{ route('facilities.index') }}" @if(request()->routeIs('facilities.*')) aria-current="page" @endif><x-icon name="search" :size="18" /><span>Cari fasilitas</span></a>
        <a href="{{ auth()->check() ? $accountUrl : route('login') }}"><x-icon name="users" :size="18" /><span>{{ auth()->check() ? 'Akun saya' : 'Masuk' }}</span></a>
    </nav>
@endif
@stack('scripts')
</body>
</html>
