@php
    $maximum = max(1, (int) $months->max('received'), (int) $months->max('completed'));
@endphp
<section class="panel min-w-0" aria-labelledby="monthly-trend-title">
    <h2 id="monthly-trend-title">Tren laporan masyarakat</h2>
    <p class="mt-2 text-xs leading-5 text-slate-600">Laporan masuk berdasarkan tanggal pengiriman dan laporan selesai berdasarkan tanggal di riwayat status.</p>
    @if($months->isEmpty())
        <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">Belum ada laporan pada rentang ini.</p>
    @else
        <div class="chart-legend" aria-hidden="true">
            <span class="inline-flex items-center gap-2"><span class="legend-dot bg-blue-700"></span>Laporan masuk</span>
            <span class="inline-flex items-center gap-2"><span class="legend-dot bg-emerald-500"></span>Laporan selesai</span>
        </div>
        <div class="mt-5 flex min-w-0 gap-4 overflow-x-auto border-b border-slate-200 pb-3" aria-hidden="true">
            @foreach($months as $month)
                <div class="flex min-w-14 flex-1 flex-col items-center gap-2">
                    <div class="flex h-40 items-end justify-center gap-1.5">
                        <span class="w-4 rounded-t bg-blue-700" title="{{ $month['received'] }} laporan masuk" style="height: {{ $month['received'] / $maximum * 100 }}%"></span>
                        <span class="w-4 rounded-t bg-emerald-500" title="{{ $month['completed'] }} laporan selesai" style="height: {{ $month['completed'] / $maximum * 100 }}%"></span>
                    </div>
                    <span class="whitespace-nowrap text-xs text-slate-600">{{ \Illuminate\Support\Carbon::createFromFormat('!Y-m', $month['month'])->translatedFormat('M Y') }}</span>
                </div>
            @endforeach
        </div>
        <details class="chart-data">
            <summary>Lihat tabel data tren</summary>
            <div class="table-wrap">
                <table>
                    <caption class="sr-only">Tren laporan masuk dan selesai setiap bulan</caption>
                    <thead><tr><th scope="col">Bulan</th><th scope="col">Laporan masuk</th><th scope="col">Laporan selesai</th></tr></thead>
                    <tbody>
                        @foreach($months as $month)
                            <tr><th scope="row">{{ \Illuminate\Support\Carbon::createFromFormat('!Y-m', $month['month'])->translatedFormat('F Y') }}</th><td>{{ $month['received'] }}</td><td>{{ $month['completed'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @endif
</section>
