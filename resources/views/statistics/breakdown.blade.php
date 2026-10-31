<section class="panel min-w-0" aria-labelledby="{{ $id }}-title">
    <h2 id="{{ $id }}-title" class="text-lg font-semibold">{{ $title }}</h2>
    <div class="mt-4 space-y-4" aria-hidden="true">
        @foreach ($rows as $row)
            <div>
                <p class="mb-1 text-sm break-words">{{ $row->label }}</p>
                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-teal-700" style="width: {{ $reportTotal > 0 ? round($row->total / $reportTotal * 100, 2) : 0 }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="table-wrap mt-5">
        <table>
            <caption class="sr-only">{{ $title }}: data pendamping grafik</caption>
            <thead><tr><th scope="col">{{ $id === 'status' ? 'Status' : 'Kategori' }}</th><th scope="col">Jumlah</th></tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr><th scope="row">{{ $row->label }}{{ isset($row->is_active) && ! $row->is_active ? ' (nonaktif)' : '' }}</th><td>{{ number_format($row->total, 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="2">Belum ada data laporan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
