@extends('layouts.app')

@section('title', 'Antrean Verifikasi')
@section('breadcrumb', 'Antrean Verifikasi')

@section('content')
<div class="page-heading">
    <div>
        <h1>Verifikasi laporan masyarakat</h1>
        <p>Periksa kesesuaian fasilitas, uraian, dan bukti sebelum menetapkan keputusan.</p>
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <x-workspace-metric label="Menunggu verifikasi" :value="$statistik['menunggu']" :description="$laporanHariIni.' masuk hari ini · '.$laporanSebelumnya.' sebelumnya'" icon="report" />
    <x-workspace-metric label="Diverifikasi hari ini" :value="$statistik['terverifikasi']" description="Siap diteruskan ke pengelola" icon="shield" />
    <x-workspace-metric label="Ditolak bulan ini" :value="$statistik['ditolak']" description="Alasan tersedia di riwayat" icon="close" />
</div>

<div class="grid min-w-0 items-start gap-5 lg:grid-cols-[minmax(14rem,1fr)_minmax(0,2.55fr)]">
    <section class="grid min-w-0 gap-3" aria-label="Daftar antrean">
        <form action="{{ route('verifikasi.antrean') }}" method="GET" class="grid gap-1.5">
            <label for="search-report">Cari nomor atau fasilitas</label>
            <div class="relative">
                <input type="search" id="search-report" name="search" value="{{ is_string(request('search')) ? request('search') : '' }}" placeholder="Cari laporan" class="field !mt-0 !pr-11">
                <button type="submit" class="icon-button absolute inset-y-0 right-1" aria-label="Cari laporan"><x-icon name="search" :size="17" /></button>
            </div>
        </form>
        <nav class="flex gap-2" aria-label="Tahap verifikasi">
            <a class="module-tab is-active" aria-current="page" href="{{ route('verifikasi.antrean') }}">Masuk ({{ $laporan->total() }})</a>
            <a class="module-tab" href="{{ route('verifikasi.riwayat') }}">Riwayat</a>
        </nav>
        @forelse($laporan as $item)
            <a href="{{ route('verifikasi.antrean', [...request()->except('laporan'), 'laporan' => $item->getKey()]) }}#rincian-laporan" @class([
                'grid gap-2 rounded-xl border p-4 shadow-xs hover:border-blue-500',
                'border-blue-600 bg-blue-50' => $selected?->is($item),
                'border-slate-300 bg-white' => ! $selected?->is($item),
            ]) @if($selected?->is($item)) aria-current="true" @endif>
                <span class="text-xs font-bold text-primary">{{ $item->report_number }}</span>
                <h2 class="line-clamp-2 text-base font-bold">{{ $item->report_title ?: $item->description }}</h2>
                <span class="text-sm text-slate-600">{{ $item->facility?->name ?? 'Fasilitas tidak tersedia' }}</span>
                <span class="badge w-fit bg-blue-50 text-primary">Laporan masuk</span>
                <span class="text-xs text-slate-600">{{ $item->created_at?->translatedFormat('j M Y, H.i') }}</span>
            </a>
        @empty
            <p class="panel text-sm text-slate-600">Belum ada laporan yang menunggu verifikasi.</p>
        @endforelse
        <p class="text-xs text-slate-600">Menampilkan {{ $laporan->count() }} dari {{ $laporan->total() }} laporan</p>
        @if($laporan->previousPageUrl())
            <a class="button-secondary" href="{{ $laporan->previousPageUrl() }}">Laporan sebelumnya</a>
        @endif
        @if($laporan->nextPageUrl())
            <a class="button-secondary" href="{{ $laporan->nextPageUrl() }}">Muat laporan berikutnya</a>
        @endif
    </section>

    @if($selected)
        <div id="rincian-laporan" class="grid min-w-0 scroll-mt-6 gap-5" x-data="verificationReview(@js('aksesibel:verifikasi:'.auth()->id().':'.$selected->getKey()), @js((string) old('status_id', '')), @js((string) $decisions->get('Ditolak', '')), @js((string) old('notes', '')))">
            <section class="panel grid gap-4" aria-labelledby="incoming-report-heading">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-primary">{{ $selected->report_number }}</p>
                        <h2 id="incoming-report-heading" class="mt-1 !text-xl">{{ $selected->report_title ?: $selected->description }}</h2>
                    </div>
                    <span class="badge bg-blue-50 text-primary">Laporan masuk</span>
                </div>
                <div class="grid gap-2 border-y border-slate-200 py-3 text-sm text-slate-600 sm:grid-cols-2">
                    <p>Pelapor {{ $selected->user?->name ?? 'Tidak tersedia' }}</p>
                    <p>Masuk {{ $selected->created_at?->translatedFormat('j M Y, H.i') }}</p>
                </div>
                <div>
                    <p class="font-semibold">{{ $selected->facility?->name }}@if($selected->specific_location) · {{ $selected->specific_location }}@endif</p>
                    @if($selected->facilityAccessibility?->accessibilityType)
                        <p class="mt-1 text-sm text-slate-600">{{ $selected->facilityAccessibility->accessibilityType->type_name }}</p>
                    @endif
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $selected->description }}</p>
                </div>
                <div>
                    <h3 class="mb-3 font-bold">Bukti yang dilampirkan</h3>
                    <div class="grid gap-3">
                        @forelse($selected->evidences as $evidence)
                            <figure class="grid gap-2">
                                <img src="{{ asset('storage/'.$evidence->file_path) }}" alt="Foto bukti {{ $loop->iteration }} untuk {{ $selected->report_number }}" class="aspect-[3.2] w-full rounded-lg object-cover" loading="lazy">
                                <figcaption class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="min-w-0 break-all text-xs text-slate-600">{{ basename($evidence->file_path) }}@if($evidence->uploaded_at) · {{ $evidence->uploaded_at->translatedFormat('j M Y') }}@endif</span>
                                    <a class="button-secondary !min-h-9" href="{{ asset('storage/'.$evidence->file_path) }}" target="_blank" rel="noopener">Perbesar foto <span class="sr-only">bukti {{ $loop->iteration }}</span></a>
                                </figcaption>
                            </figure>
                        @empty
                            <p class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600">Belum ada bukti yang dilampirkan.</p>
                        @endforelse
                    </div>
                </div>
                <fieldset class="grid gap-3">
                    <legend class="mb-3 text-lg font-bold">Daftar pemeriksaan</legend>
                    @foreach([
                        'facility' => 'Fasilitas dan jenis sarana sesuai',
                        'location' => 'Lokasi kejadian dapat dikenali',
                        'evidence' => 'Bukti menunjukkan kendala yang dilaporkan',
                    ] as $value => $check)
                        <label class="flex min-h-9 items-center gap-3 text-sm font-normal">
                            <input type="checkbox" value="{{ $value }}" x-model="checks" class="size-5 shrink-0 accent-blue-700" />{{ $check }}
                        </label>
                    @endforeach
                </fieldset>
            </section>

            <section class="panel" aria-labelledby="decision-title">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="decision-title">Keputusan verifikasi</h2>
                    <span class="module-tab !min-h-9 !py-1.5 text-xs">Belum dikirim</span>
                </div>
                @if($decisions->isNotEmpty())
                    <form action="{{ route('verifikasi.status.update', $selected) }}" method="POST" class="mt-4 grid gap-4">
                        @csrf
                        <fieldset class="flex flex-wrap gap-2">
                            <legend class="sr-only">Pilih keputusan</legend>
                            @foreach(['Terverifikasi' => 'Terima & verifikasi', 'Ditolak' => 'Tolak laporan'] as $status => $label)
                                @if($decisions->has($status))
                                    <label class="flex min-h-11 items-center gap-2 rounded-lg border border-slate-300 px-3 text-sm font-medium has-checked:border-blue-600 has-checked:bg-blue-50">
                                        <input type="radio" name="status_id" value="{{ $decisions->get($status) }}" x-model="decision" class="accent-blue-700" @checked((string) old('status_id') === (string) $decisions->get($status)) required>
                                        {{ $label }}
                                    </label>
                                @endif
                            @endforeach
                        </fieldset>
                        <div>
                            <label for="decision-notes" x-text="decision && decision === rejected ? 'Alasan penolakan *' : 'Catatan verifikasi'">Catatan verifikasi</label>
                            <textarea id="decision-notes" name="notes" class="field" rows="4" maxlength="2000" x-model="notes" :required="decision && decision === rejected">{{ old('notes') }}</textarea>
                        </div>
                        <div x-cloak x-show="decision && decision === rejected" class="flex items-start gap-3 rounded-lg bg-amber-50 p-4 text-sm text-amber-900">
                            <x-icon name="info" :size="18" /><p>Alasan penolakan dicatat di riwayat. Laporan yang ditolak berhenti di tahap verifikasi.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button class="button-secondary" type="button" @click="saveDraft()">Simpan pemeriksaan</button>
                            <button class="button" type="submit" :class="{ 'button-danger': decision && decision === rejected }" x-text="decision && decision === rejected ? 'Tolak laporan' : 'Simpan keputusan'">Simpan keputusan</button>
                        </div>
                        <p x-cloak x-show="feedback" x-text="feedback" role="status" class="text-sm text-slate-600"></p>
                        <a class="w-fit text-sm font-semibold text-primary hover:underline" href="{{ route('verifikasi.status', ['kelola' => $selected->getKey()]) }}">Kelola status laporan</a>
                    </form>
                @endif
            </section>
        </div>
    @endif
</div>
@endsection
