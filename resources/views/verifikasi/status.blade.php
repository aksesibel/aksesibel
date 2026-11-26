@extends('layouts.app')

@section('title', 'Kelola Status Verifikasi')
@section('breadcrumb', 'Kelola status')

@section('content')
<div class="page-heading">
    <div>
        <h1>Kelola Status Verifikasi</h1>
        <p>Pantau tahapan laporan masyarakat dan perbarui status verifikasinya.</p>
    </div>
</div>

<div class="grid grid-cols-2 gap-3 xl:grid-cols-4 xl:gap-4">
    <x-workspace-metric label="Menunggu verifikasi" :value="$statistik['menunggu']" description="Laporan belum diperiksa" icon="report" />
    <x-workspace-metric label="Terverifikasi" :value="$statistik['terverifikasi']" description="Laporan telah dinyatakan sesuai" icon="shield" />
    <x-workspace-metric label="Ditolak" :value="$statistik['ditolak']" description="Laporan tidak memenuhi kriteria" icon="close" />
    <x-workspace-metric label="Dalam tindak lanjut" :value="$statistik['lanjutan']" description="Laporan diteruskan untuk penanganan" icon="tool" />
</div>

@if($detail)
    <section class="panel grid scroll-mt-5 gap-5" id="ubah-status" aria-labelledby="status-detail-heading">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="status-detail-heading">Detail laporan dan perubahan status</h2>
            <span class="badge bg-blue-50 text-primary">{{ $detail->report_number }}</span>
        </div>
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.65fr)_minmax(0,1fr)]">
            <div class="grid gap-3">
                <h3 class="text-lg font-bold">{{ $detail->report_title ?: ($detail->issueCategory?->category_name ?? 'Laporan masyarakat') }}</h3>
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-600">Fasilitas</dt><dd class="mt-1 font-semibold">{{ $detail->facility?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-600">Tanggal laporan</dt><dd class="mt-1 font-semibold">{{ $detail->created_at?->translatedFormat('d F Y, H.i') ?? '—' }}@if($detail->created_at) WIB @endif</dd></div>
                </dl>
                <p class="whitespace-pre-line text-sm leading-6 text-slate-600">{{ $detail->description }}</p>
                @if($detail->evidences->isNotEmpty())
                    <ul class="flex flex-wrap gap-2" aria-label="Bukti laporan">
                        @foreach($detail->evidences as $evidence)
                            <li><a class="button-secondary !min-h-9 !px-3 !py-1.5" href="{{ asset('storage/'.$evidence->file_path) }}" target="_blank" rel="noopener">Lihat bukti {{ $loop->iteration }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <dl class="grid content-start gap-3 text-sm">
                <div><dt class="text-xs text-slate-600">Status saat ini</dt><dd class="mt-1"><x-verification-status-badge :status="$detail->status?->status_name" /></dd></div>
                <div><dt class="text-xs text-slate-600">Terakhir diperbarui</dt><dd class="mt-1 font-semibold">{{ $detail->updated_at?->translatedFormat('d F Y, H.i') ?? '—' }}@if($detail->updated_at) WIB @endif</dd></div>
            </dl>
        </div>
        <form action="{{ route('verifikasi.status.update', [$detail, ...$listQuery]) }}" method="POST" class="grid gap-4 border-t border-slate-200 pt-5" aria-label="Ubah status laporan">
            @csrf
            <fieldset>
                <legend class="mb-2 text-sm font-semibold text-slate-700">Ubah status menjadi</legend>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-[repeat(auto-fit,minmax(9.25rem,1fr))]">
                    @foreach($daftarStatus as $status)
                        <label class="mb-0 flex min-h-12 cursor-pointer items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:border-blue-500 has-checked:border-blue-600 has-checked:bg-blue-50">
                            <input type="radio" name="status_id" value="{{ $status->getKey() }}" @checked((int) old('status_id', $detail->status_id) === $status->getKey()) required class="shrink-0 accent-blue-700">
                            <span>{{ $status->status_name }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div>
                <label for="notes">Catatan perubahan status</label>
                <textarea class="field" name="notes" id="notes" rows="3" maxlength="2000" placeholder="Tuliskan alasan atau catatan perubahan status">{{ old('notes') }}</textarea>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs leading-5 text-slate-600">Perubahan status dan catatan akan tercatat setelah disimpan.</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('verifikasi.status', $listQuery) }}#daftar-status" class="button-secondary">Batal</a>
                    <button type="submit" class="button">Simpan perubahan</button>
                </div>
            </div>
        </form>
    </section>
@endif

<section class="panel scroll-mt-5 overflow-hidden !p-0" id="daftar-status" aria-labelledby="status-list-heading">
    <div class="grid gap-4 p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="status-list-heading">Daftar status laporan</h2>
            <span class="badge bg-blue-50 text-primary">{{ $laporan->total() }} laporan</span>
        </div>
        <form action="{{ route('verifikasi.status') }}#daftar-status" method="GET" class="grid items-end gap-3 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1fr)_auto]" aria-label="Filter daftar status">
            <div>
                <label for="search">Cari laporan</label>
                <div class="relative">
                    <x-icon name="search" :size="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-slate-500" />
                    <input class="field !pl-9" type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nomor, judul, atau fasilitas">
                </div>
            </div>
            <div>
                <label for="status">Status verifikasi</label>
                <select class="field" name="status" id="status">
                    <option value="">Semua status</option>
                    @foreach($daftarStatus as $status)
                        <option value="{{ $status->getKey() }}" @selected(($filters['status'] ?? null) === $status->getKey())>{{ $status->status_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tanggal">Tanggal laporan</label>
                <input class="field" type="date" name="tanggal" id="tanggal" value="{{ $filters['tanggal'] ?? '' }}">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="button-secondary">Cari</button>
                @if($filters)
                    <a href="{{ route('verifikasi.status') }}#daftar-status" class="button-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
    <div class="relative max-w-full overflow-x-auto">
        <table class="min-w-[890px]" aria-label="Tabel status laporan">
            <thead>
                <tr>
                    <th scope="col">Nomor laporan</th><th scope="col">Uraian laporan</th><th scope="col">Fasilitas</th>
                    <th scope="col">Tanggal laporan</th><th scope="col">Status saat ini</th><th scope="col">Terakhir diperbarui</th><th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporan as $item)
                    @php
                        $isSelected = $detail?->is($item);
                        $manageUrl = route('verifikasi.status', [...$listQuery, 'kelola' => $item->getKey()]).'#ubah-status';
                    @endphp
                    <tr @class(['!bg-blue-50' => $isSelected])>
                        <td class="whitespace-nowrap"><a class="!font-semibold" href="{{ $manageUrl }}">{{ $item->report_number }}</a></td>
                        <td class="min-w-40 font-semibold">{{ $item->report_title ?: $item->description }}</td>
                        <td class="min-w-32 text-sm">{{ $item->facility?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap text-xs text-slate-600">{{ $item->created_at?->translatedFormat('d M Y') ?? '—' }}</td>
                        <td><x-verification-status-badge :status="$item->status?->status_name" class="whitespace-nowrap" /></td>
                        <td class="whitespace-nowrap text-xs text-slate-600">
                            @if($item->updated_at)
                                <time datetime="{{ $item->updated_at->toIso8601String() }}">{{ $item->updated_at->translatedFormat('d M Y') }}<br>{{ $item->updated_at->format('H.i') }} WIB</time>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <a class="button-secondary !min-h-9 !px-3 !py-1.5" href="{{ $manageUrl }}" @if($isSelected) aria-current="true" @endif>Kelola<span class="sr-only"> {{ $item->report_number }}</span></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-sm text-slate-600">Tidak ada laporan yang ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 sm:px-5">
        <p class="text-xs text-slate-600">Menampilkan {{ $laporan->firstItem() ?? 0 }}–{{ $laporan->lastItem() ?? 0 }} dari {{ $laporan->total() }} laporan</p>
        {{ $laporan->links() }}
    </div>
</section>
@endsection
