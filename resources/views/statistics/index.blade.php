<x-layout title="Rekap dan Statistik" subtitle="Pantau laporan aksesibilitas berdasarkan data yang tercatat. Semua ringkasan laporan dan tabel mengikuti filter yang sama.">

    @if ($missingDependencies !== [])
        <section class="panel" aria-labelledby="dependency-title">
            <h2 id="dependency-title" class="text-lg font-semibold">Statistik belum dapat ditampilkan</h2>
            <p class="mt-2">Selesaikan migration modul berikut pada database aplikasi:</p>
            <ul class="mt-3 list-disc space-y-2 pl-5">
                @foreach ($missingDependencies as $table => $message)
                    <li><code>{{ $table }}</code>: {{ $message }}</li>
                @endforeach
            </ul>
        </section>
    @else
        @php
            $completedTotal = (int) $byStatus->where('label', 'Selesai')->sum('total');
            $followUpTotal = (int) $byStatus->where('label', 'Dalam Tindak Lanjut')->sum('total');
            $summaryDownload = implode("\r\n", [
                'Indikator,Jumlah',
                "Jumlah fasilitas (global),{$facilityTotal}",
                "Jumlah laporan sesuai filter,{$reportTotal}",
                "Laporan selesai,{$completedTotal}",
                "Dalam tindak lanjut,{$followUpTotal}",
            ]);
        @endphp
        <x-slot:actions>
            <a class="button-secondary" download="ringkasan-aksesibel.csv" href="data:text/csv;charset=utf-8,{{ rawurlencode($summaryDownload) }}"><x-icon name="report" :size="16" /> Unduh ringkasan</a>
        </x-slot:actions>
        <form method="GET" action="{{ route('manage.statistics') }}" aria-label="Filter statistik">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="period_id">Periode pelaporan</label>
                    <select id="period_id" name="period_id" class="field">
                        <option value="">Semua periode</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period->period_id }}" @selected(($filters['period_id'] ?? '') == $period->period_id)>{{ $period->period_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category_id">Kategori fasilitas</label>
                    <select id="category_id" name="category_id" class="field">
                        <option value="">Semua kategori fasilitas</option>
                        @foreach ($facilityCategories as $category)
                            <option value="{{ $category->category_id }}" @selected(($filters['category_id'] ?? '') == $category->category_id)>{{ $category->category_name }}{{ $category->is_active ? '' : ' (nonaktif)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="issue_category_id">Kategori kendala</label>
                    <select id="issue_category_id" name="issue_category_id" class="field">
                        <option value="">Semua kategori kendala</option>
                        @foreach ($issueCategories as $category)
                            <option value="{{ $category->issue_category_id }}" @selected(($filters['issue_category_id'] ?? '') == $category->issue_category_id)>{{ $category->category_name }}{{ $category->is_active ? '' : ' (nonaktif)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="region_id">Wilayah</label>
                    <select id="region_id" name="region_id" class="field">
                        <option value="">Semua wilayah</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_id }}" @selected(($filters['region_id'] ?? '') == $region->region_id)>{{ $region->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="mt-3 text-xs leading-5 text-slate-500">Periode memakai hubungan periode pada laporan. Kategori nonaktif tetap tersedia untuk data historis.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <button type="submit" class="button">Terapkan filter</button>
                <a href="{{ route('manage.statistics') }}" class="button-secondary">Reset filter</a>
            </div>
        </form>

        <div class="stat-cards">
            <section class="panel" aria-labelledby="facility-total-title">
                <div class="stat-card-heading"><h2 id="facility-total-title">Jumlah fasilitas — total global</h2><x-icon name="building" /></div>
                <p class="stat-value">{{ number_format($facilityTotal, 0, ',', '.') }}</p>
                <p class="stat-description">Seluruh fasilitas terdaftar, termasuk nonaktif; tidak mengikuti filter laporan.</p>
            </section>
            <section class="panel" aria-labelledby="report-total-title">
                <div class="stat-card-heading"><h2 id="report-total-title">Jumlah laporan sesuai filter</h2><x-icon name="report" /></div>
                <p class="stat-value">{{ number_format($reportTotal, 0, ',', '.') }}</p>
                <p class="stat-description">Setiap laporan dihitung sekali pada seluruh status verifikasi.</p>
            </section>
            <section class="panel" aria-labelledby="completed-total-title">
                <div class="stat-card-heading"><h2 id="completed-total-title">Laporan selesai</h2><x-icon name="check" /></div>
                <p class="stat-value">{{ number_format($completedTotal, 0, ',', '.') }}</p>
                <p class="stat-description">{{ $reportTotal > 0 ? number_format($completedTotal / $reportTotal * 100, 1, ',', '.') : '0' }}% dari laporan sesuai filter.</p>
            </section>
            <section class="panel" aria-labelledby="follow-up-total-title">
                <div class="stat-card-heading"><h2 id="follow-up-total-title">Dalam tindak lanjut</h2><x-icon name="tool" /></div>
                <p class="stat-value">{{ number_format($followUpTotal, 0, ',', '.') }}</p>
                <p class="stat-description">Laporan dengan status terakhir Dalam Tindak Lanjut.</p>
            </section>
        </div>

        @if ($reportTotal === 0)
            <div class="panel mb-6" role="status">Belum ada laporan yang sesuai dengan filter. Ubah pilihan atau gunakan reset filter.</div>
        @endif

        <div class="statistics-charts">
            @include('statistics.monthly-trend', ['months' => $byMonth])
            @include('statistics.breakdown', ['title' => 'Kondisi sarana terdata', 'id' => 'condition', 'rows' => $byCondition, 'reportTotal' => $byCondition->sum('total'), 'variant' => 'donut', 'description' => 'Jumlah sarana mengikuti filter kategori fasilitas dan wilayah.'])
        </div>
        <div class="statistics-charts">
            @include('statistics.breakdown', ['title' => 'Distribusi menurut wilayah', 'id' => 'region', 'rows' => $byRegion, 'reportTotal' => $reportTotal, 'variant' => 'bars'])
            @include('statistics.breakdown', ['title' => 'Laporan berdasarkan kategori fasilitas', 'id' => 'facility', 'rows' => $byFacility, 'reportTotal' => $reportTotal, 'variant' => 'columns'])
        </div>
        @include('statistics.breakdown', ['title' => 'Laporan berdasarkan kategori kendala', 'id' => 'issue', 'rows' => $byIssue, 'reportTotal' => $reportTotal, 'variant' => 'donut'])

        <div class="statistics-recap">
        @include('statistics.breakdown', ['title' => 'Laporan berdasarkan status', 'id' => 'status', 'rows' => $byStatus, 'reportTotal' => $reportTotal, 'variant' => 'bars'])
        <section class="panel" aria-labelledby="recap-title">
            <h2 id="recap-title" class="text-lg font-semibold">Tabel rekap laporan</h2>
            <p class="mt-2 mb-4 text-sm text-slate-600">Dikelompokkan menurut kategori fasilitas, kategori kendala, dan status. Total seluruh kelompok sesuai filter: {{ number_format($reportTotal, 0, ',', '.') }} laporan.</p>
            <div class="table-wrap">
                <table>
                    <caption class="sr-only">Jumlah laporan per kategori fasilitas, kategori kendala, dan status sesuai filter</caption>
                    <thead><tr><th scope="col">Kategori fasilitas</th><th scope="col">Kategori kendala</th><th scope="col">Status</th><th scope="col">Jumlah laporan</th></tr></thead>
                    <tbody>
                        @forelse ($recap as $row)
                            <tr>
                                <td>{{ $row->facility_category }}{{ $row->facility_category_active ? '' : ' (nonaktif)' }}</td>
                                <td>{{ $row->issue_category }}{{ $row->issue_category_active ? '' : ' (nonaktif)' }}</td>
                                <td>{{ $row->status_name }}</td>
                                <td>{{ number_format($row->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">
                                @if ($reportTotal > 0)
                                    Halaman ini tidak memiliki rekap.
                                    <a class="underline" href="{{ route('manage.statistics', array_diff_key($filters, ['page' => true])) }}">Kembali ke halaman pertama.</a>
                                @else
                                    Tidak ada rekap laporan yang sesuai dengan filter.
                                @endif
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $recap->links() }}</div>
        </section>
        </div>
    @endif
</x-layout>
