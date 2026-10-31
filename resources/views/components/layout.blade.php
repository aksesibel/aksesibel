@props(['title' => 'Aksesibel'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Aksesibel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
<a href="#konten" class="skip-link">Lewati ke konten</a>
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
    @can('manage-supporting-data')
        <nav aria-label="Modul pengelola" class="border-t border-slate-100">
            <div class="mx-auto flex max-w-7xl flex-wrap gap-2 px-4 py-3 sm:px-6">
            @foreach(['users' => 'Pengguna dan Hak Akses', 'facility-categories' => 'Kategori Fasilitas', 'issue-categories' => 'Kategori Kendala', 'reporting-periods' => 'Periode Pelaporan', 'statistics' => 'Rekap dan Statistik'] as $key => $label)
                @php($active = request()->routeIs('manage.'.$key.'*'))
                <a href="{{ route('manage.'.$key.($key === 'statistics' ? '' : '.index')) }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ $active ? 'bg-teal-800 text-white' : 'text-slate-700 hover:bg-slate-100' }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
            </div>
        </nav>
    @endcan
</header>
<main id="konten" class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6" tabindex="-1">
    <div><p class="mb-1 text-xs font-semibold uppercase tracking-widest text-teal-700">Aksesibel</p><h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h1></div>
    @if(session('success'))<div role="status" class="rounded-lg border border-teal-300 bg-teal-50 p-4 text-teal-900">{{ session('success') }}</div>@endif
    @if(session('error'))<div role="alert" class="rounded-lg border border-red-300 bg-red-50 p-4 text-red-900">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div role="alert" class="rounded-lg border border-red-300 bg-red-50 p-4 text-red-900"><h2 class="font-semibold">Periksa kembali data berikut:</h2><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    {{ $slot }}
</main>
<footer class="mx-auto max-w-7xl px-4 py-8 text-xs text-slate-500 sm:px-6">Aksesibel · Modul Data Pendukung dan Statistik</footer>
</body>
</html>
