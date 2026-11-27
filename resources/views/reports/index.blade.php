@extends('layouts.masyarakat')

@section('title', 'Laporan Saya')
@section('breadcrumb', 'Laporan Saya')

@section('content')
<div class="page-heading">
    <div>
        <h1>Laporan saya dan pemantauan</h1>
        <p>Pantau laporan Anda dan lihat pembaruan tindak lanjut.</p>
    </div>
    <a href="{{ route('reports.create') }}" class="button"><x-icon name="plus" :size="16" /> Buat laporan</a>
</div>

@if(session('info'))<div class="information-note" role="status"><x-icon name="info" :size="18" />{{ session('info') }}</div>@endif

<div class="grid gap-4 sm:grid-cols-3">
    @foreach([['Total laporan saya', $summary['total'], 'report'], ['Laporan aktif', $summary['active'], 'tool'], ['Laporan selesai', $summary['completed'], 'check']] as [$label, $value, $icon])
        <section class="panel">
            <div class="flex items-center justify-between gap-2 text-sm text-slate-600">{{ $label }} <x-icon :name="$icon" :size="18" class="text-primary" /></div>
            <p class="mt-3 text-3xl font-bold">{{ $value }}</p>
        </section>
    @endforeach
</div>

<div class="panel grid gap-5">
    <h2>Daftar laporan</h2>
    <form action="{{ route('reports.index') }}" method="GET" class="grid items-end gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)_minmax(0,1fr)_auto]">
        <div><label for="report-search">Cari laporan</label><input id="report-search" name="search" type="search" class="field" value="{{ $search }}" placeholder="Nomor, judul, atau fasilitas"></div>
        <div>
            <label for="report-status">Status laporan</label>
            <select id="report-status" name="status_id" class="field">
                <option value="">Semua status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->status_id }}" @selected((string) $statusId === (string) $status->status_id)>{{ $status->status_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="report-order">Urutan</label>
            <select id="report-order" name="sort" class="field">
                <option value="latest" @selected($sort === 'latest')>Terbaru</option>
                <option value="oldest" @selected($sort === 'oldest')>Terlama</option>
            </select>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="button">Filter</button>
            @if($search !== '' || $statusId !== null || $sort === 'oldest')
                <a href="{{ route('reports.index') }}" class="button-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="grid gap-3">
        @forelse($reports as $report)
            @php($statusName = $report->status?->status_name ?? 'Status belum tersedia')
            <article class="grid min-w-0 gap-3 rounded-xl border border-slate-300 p-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                <div class="min-w-0">
                    <p class="mb-1 text-xs font-bold text-primary">{{ $report->report_number }}</p>
                    <h3 class="truncate font-semibold">{{ $report->report_title ?: $report->description }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $report->facility?->name ?? 'Fasilitas belum tersedia' }}@if($report->issueCategory) · {{ $report->issueCategory->category_name }}@endif</p>
                    @if($report->created_at)<time datetime="{{ $report->created_at->toIso8601String() }}" class="mt-1 block text-xs text-slate-600">{{ $report->created_at->translatedFormat('j M Y, H.i') }}</time>@endif
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 sm:flex-col sm:items-end">
                    <span @class(['badge text-xs', 'bg-emerald-50 text-emerald-800' => $statusName === 'Selesai', 'bg-red-50 text-red-800' => $statusName === 'Ditolak', 'bg-blue-50 text-primary' => !in_array($statusName, ['Selesai', 'Ditolak'], true)])>{{ $statusName }}</span>
                    <a class="inline-flex min-h-9 items-center gap-1 text-sm font-semibold text-primary hover:underline" href="{{ route('reports.show', $report) }}">Lihat kronologi <x-icon name="arrow" :size="15" /></a>
                </div>
            </article>
        @empty
            <div class="rounded-lg bg-slate-50 p-6 text-center">
                <p class="font-semibold">{{ $search !== '' || $statusId !== null ? 'Laporan tidak ditemukan' : 'Belum ada laporan' }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ $search !== '' || $statusId !== null ? 'Coba ubah pencarian atau filter.' : 'Laporan yang Anda kirim akan muncul di sini.' }}</p>
            </div>
        @endforelse
    </div>

    {{ $reports->links() }}
</div>
@endsection
