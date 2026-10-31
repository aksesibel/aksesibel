<x-layout title="Rekap dan Statistik">
    <p class="mb-6 text-slate-600">Pantau laporan aksesibilitas berdasarkan data yang tercatat. Semua ringkasan laporan dan tabel mengikuti filter yang sama.</p>

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
        <form method="GET" action="{{ route('manage.statistics') }}" class="panel mb-6" aria-label="Filter statistik">
            <div class="grid gap-4 md:grid-cols-3">
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
            </div>
            <p class="mt-3 text-sm text-slate-600">Periode memakai hubungan periode pada laporan, bukan tanggal laporan dibuat. Kategori nonaktif tetap tersedia untuk data historis.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <button type="submit" class="button">Terapkan filter</button>
                <a href="{{ route('manage.statistics') }}" class="button-secondary">Reset filter</a>
            </div>
        </form>

        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            <section class="panel" aria-labelledby="facility-total-title">
                <h2 id="facility-total-title" class="text-sm font-medium text-slate-600">Jumlah fasilitas — total global</h2>
                <p class="mt-2 text-4xl font-bold text-slate-900">{{ number_format($facilityTotal, 0, ',', '.') }}</p>
                <p class="mt-2 text-sm text-slate-600">Seluruh fasilitas terdaftar, termasuk nonaktif; tidak mengikuti filter laporan.</p>
            </section>
            <section class="panel" aria-labelledby="report-total-title">
                <h2 id="report-total-title" class="text-sm font-medium text-slate-600">Jumlah laporan sesuai filter</h2>
                <p class="mt-2 text-4xl font-bold text-slate-900">{{ number_format($reportTotal, 0, ',', '.') }}</p>
                <p class="mt-2 text-sm text-slate-600">Setiap laporan dihitung sekali pada seluruh status verifikasi.</p>
            </section>
        </div>

        @if ($reportTotal === 0)
            <div class="panel mb-6" role="status">Belum ada laporan yang sesuai dengan filter. Ubah pilihan atau gunakan reset filter.</div>
        @endif

        <div class="mb-6 grid gap-6 xl:grid-cols-3">
            @include('statistics.breakdown', ['title' => 'Laporan berdasarkan status', 'id' => 'status', 'rows' => $byStatus, 'reportTotal' => $reportTotal])
            @include('statistics.breakdown', ['title' => 'Laporan berdasarkan kategori kendala', 'id' => 'issue', 'rows' => $byIssue, 'reportTotal' => $reportTotal])
            @include('statistics.breakdown', ['title' => 'Laporan berdasarkan kategori fasilitas', 'id' => 'facility', 'rows' => $byFacility, 'reportTotal' => $reportTotal])
        </div>

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
    @endif
</x-layout>
