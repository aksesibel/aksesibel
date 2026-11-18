<x-layout title="Periode Pelaporan" subtitle="Kelola rentang pelaporan agar periode laporan dapat ditentukan otomatis dari tanggal pembuatannya.">
    <x-slot:actions><a class="button" href="{{ route('manage.reporting-periods.create') }}"><x-icon name="plus" />Tambah periode</a></x-slot:actions>
    <section class="panel">
    <div class="section-heading"><div><h2>Daftar periode pelaporan</h2><p>Gunakan rentang tanggal yang tidak tumpang tindih.</p></div><span class="badge">{{ $periods->total() }} periode</span></div>
    <form method="GET" action="{{ route('manage.reporting-periods.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="grow">
            <label for="q">Cari nama periode</label>
            <input class="field" id="q" name="q" value="{{ request('q') }}" maxlength="255">
        </div>
        <button class="button" type="submit">Cari periode</button>
        <a class="button-secondary" href="{{ route('manage.reporting-periods.index') }}">Reset filter</a>
    </form>
        <div class="table-wrap">
            <table>
                <caption class="sr-only">Daftar periode pelaporan</caption>
                <thead><tr><th scope="col">Nama periode</th><th scope="col">Tanggal mulai</th><th scope="col">Tanggal selesai</th><th scope="col">Laporan</th><th scope="col">Tindakan</th></tr></thead>
                <tbody>
                    @forelse ($periods as $period)
                        <tr>
                            <td><a href="{{ route('manage.reporting-periods.show', $period) }}">{{ $period->period_name }}</a></td>
                            <td>{{ $period->start_date->format('d/m/Y') }}</td>
                            <td>{{ $period->end_date->format('d/m/Y') }}</td>
                            <td>{{ $period->reports_count }}</td>
                            <td><a class="inline-flex items-center gap-2" href="{{ route('manage.reporting-periods.edit', $period) }}" aria-label="Edit periode {{ $period->period_name }}">Edit <x-icon name="arrow" :size="16" /></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Tidak ada periode yang sesuai. Ubah pencarian atau tambahkan periode baru.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer"><p>{{ $periods->firstItem() ?? 0 }}–{{ $periods->lastItem() ?? 0 }} dari {{ $periods->total() }} periode</p><div>{{ $periods->links() }}</div></div>
    </section>
</x-layout>

