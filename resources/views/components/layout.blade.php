@props(['title' => 'Aksesibel', 'subtitle' => null])
@php($workspace = auth()->user()?->can('manage-supporting-data') && (request()->routeIs('manage.*') || request()->routeIs('account')))
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Aksesibel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased {{ $workspace ? 'workspace' : '' }}" @if($workspace) data-workspace @endif>
<a href="#konten" class="skip-link">Lewati ke konten</a>
@if ($workspace)
<x-workspace-navigation />
<button class="sidebar-backdrop" type="button" data-menu-close hidden aria-label="Tutup menu navigasi" tabindex="-1"></button>
<div class="workspace-content" id="workspace-content">
    <header class="workspace-topbar">
        <div class="topbar-location"><button class="icon-button menu-toggle" type="button" data-menu-toggle aria-controls="workspace-navigation" aria-expanded="false" aria-label="Buka navigasi"><x-icon name="menu" /></button><span>Aksesibel <span class="breadcrumb-divider">/</span> <span class="breadcrumb-current">{{ request()->routeIs('manage.statistics') ? 'Statistik' : (request()->routeIs('account') ? 'Akun saya' : 'Kelola data') }}</span></span></div>
        <div class="topbar-account"><span class="data-label">Data aplikasi</span><a href="{{ route('account') }}" class="role-label"><x-icon name="users" :size="18" /><span>Pengelola Fasilitas</span></a><a class="account-name" href="{{ route('account') }}">{{ auth()->user()->name }}</a></div>
    </header>
    <main id="konten" class="workspace-main" tabindex="-1">
        <div class="page-heading"><div><h1>{{ $title }}</h1>@if($subtitle)<p>{{ $subtitle }}</p>@endif</div>@isset($actions)<div class="page-actions">{{ $actions }}</div>@endisset</div>
        @if (request()->routeIs('manage.*') && ! request()->routeIs('manage.statistics'))<x-workspace-tabs />@endif
@else
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-5 sm:px-6">
        <a class="text-xl font-bold tracking-tight text-teal-800" href="{{ route('home') }}">Aksesibel<span class="ml-2 text-sm font-normal text-slate-500">Ruang publik untuk semua</span></a>
        <div class="flex flex-wrap items-center gap-4 text-sm">
            @auth
                <a href="{{ route('account') }}">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="button-secondary" type="submit">Keluar</button></form>
            @else
                <a href="{{ route('login') }}">Masuk</a><a class="button" href="{{ route('register') }}">Daftar masyarakat</a>
            @endauth
        </div>
    </div>
</header>
<main id="konten" class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6" tabindex="-1">
    <div><p class="mb-1 text-xs font-semibold uppercase tracking-widest text-teal-700">Aksesibel</p><h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h1></div>
@endif
    @if(session('success'))<div role="status" class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-900">{{ session('success') }}</div>@endif
    @if(session('error'))<div role="alert" class="rounded-lg border border-red-300 bg-red-50 p-4 text-red-900">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div role="alert" class="rounded-lg border border-red-300 bg-red-50 p-4 text-red-900"><h2 class="font-semibold">Periksa kembali data berikut:</h2><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    {{ $slot }}
</main>
@if ($workspace)
    <footer class="workspace-footer">Aksesibel · Ruang publik untuk semua<span>Data Pendukung dan Statistik</span></footer>
</div>
@else
<footer class="mx-auto max-w-7xl px-4 py-8 text-xs text-slate-500 sm:px-6">Aksesibel · Modul Data Pendukung dan Statistik</footer>
@endif
</body>
</html>
