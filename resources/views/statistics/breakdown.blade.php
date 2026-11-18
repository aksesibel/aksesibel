@php
    $colors = ['#1d4ed8', '#0891b2', '#b45309', '#15803d', '#7c3aed', '#be123c', '#475569'];
    $maximum = max(1, (int) $rows->max('total'));
    $offset = 0;
@endphp
<section class="panel min-w-0" aria-labelledby="{{ $id }}-title">
    <h2 id="{{ $id }}-title">{{ $title }}</h2>
    <p class="mt-2 text-xs leading-5 text-slate-500">{{ $id === 'status' ? 'Jumlah laporan menurut status terakhir pada periode terpilih.' : 'Distribusi laporan sesuai filter yang diterapkan.' }}</p>

    @if ($variant === 'columns' && $rows->isNotEmpty())
        <div class="chart-legend" aria-hidden="true"><span class="inline-flex items-center gap-2"><span class="legend-dot bg-blue-700"></span>Jumlah laporan</span></div>
        <div class="vertical-chart" aria-hidden="true">
            @foreach ($rows as $row)
                <div class="chart-column">
                    <span class="chart-column-count">{{ number_format($row->total, 0, ',', '.') }}</span>
                    <div class="chart-column-bar" style="height: {{ round($row->total / $maximum * 160, 2) }}px"></div>
                    <span class="chart-column-label">{{ $row->label }}</span>
                </div>
            @endforeach
        </div>
    @elseif ($variant === 'donut')
        <div class="donut-wrap" aria-hidden="true">
            <svg class="donut" viewBox="0 0 120 120" fill="none">
                <circle cx="60" cy="60" r="48" stroke="#e2e8f0" stroke-width="14" />
                @foreach ($rows as $row)
                    @php($percentage = $reportTotal > 0 ? $row->total / $reportTotal * 100 : 0)
                    <circle cx="60" cy="60" r="48" pathLength="100" stroke="{{ $colors[$loop->index % count($colors)] }}" stroke-width="14" stroke-dasharray="{{ $percentage }} {{ 100 - $percentage }}" stroke-dashoffset="{{ -$offset }}" />
                    @php($offset += $percentage)
                @endforeach
            </svg>
            <div class="donut-center"><strong>{{ number_format($reportTotal, 0, ',', '.') }}</strong><span>laporan</span></div>
        </div>
    @elseif ($variant === 'bars' && $rows->isNotEmpty())
        <div class="status-bars" aria-hidden="true">
            @foreach ($rows as $row)
                <div>
                    <div class="status-bar-label"><span class="inline-flex items-start gap-2"><x-icon name="{{ $row->label === 'Selesai' ? 'check' : 'report' }}" :size="16" />{{ $row->label }}</span><strong>{{ number_format($row->total, 0, ',', '.') }}</strong></div>
                    <div class="status-bar-track"><div class="status-bar-fill" style="width: {{ $reportTotal > 0 ? round($row->total / $reportTotal * 100, 2) : 0 }}%; background-color: {{ $row->label === 'Selesai' ? '#15803d' : ($row->label === 'Ditolak' ? '#be123c' : '#1d4ed8') }}"></div></div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($variant !== 'donut')
        <details class="chart-data" @if($rows->isEmpty()) open @endif>
            <summary>Lihat tabel data</summary>
    @endif
    <div class="table-wrap {{ $variant === 'donut' ? 'chart-legend-table mt-5' : '' }}">
        <table>
            <caption class="sr-only">{{ $title }}: data pendamping grafik</caption>
            <thead><tr><th scope="col">{{ $id === 'status' ? 'Status' : 'Kategori' }}</th><th scope="col">Jumlah</th></tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr><th scope="row">@if($variant === 'donut')<span class="legend-dot mr-2" style="background-color: {{ $colors[$loop->index % count($colors)] }}" aria-hidden="true"></span>@endif{{ $row->label }}{{ isset($row->is_active) && ! $row->is_active ? ' (nonaktif)' : '' }}</th><td>{{ number_format($row->total, 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="2">Belum ada data laporan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($variant !== 'donut')
        </details>
    @endif
</section>
