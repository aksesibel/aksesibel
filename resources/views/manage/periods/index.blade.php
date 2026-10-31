<x-layout title="Periode Pelaporan">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <p>Kelola rentang pelaporan. Periode boleh tumpang tindih.</p>
        <a class="button" href="{{ route('manage.reporting-periods.create') }}">Tambah periode</a>
    </div>
    <form method="GET" action="{{ route('manage.reporting-periods.index') }}" class="panel flex flex-wrap items-end gap-3">
        <div class="grow">
            <label for="q">Cari nama periode</label>
            <input class="field" id="q" name="q" value="{{ request('q') }}" maxlength="255">
        </div>
        <button class="button" type="submit">Cari periode</button>
        <a class="button-secondary" href="{{ route('manage.reporting-periods.index') }}">Reset filter</a>
    </form>
    <div class="panel">
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
                            <td><a href="{{ route('manage.reporting-periods.edit', $period) }}" aria-label="Edit periode {{ $period->period_name }}">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Tidak ada periode yang sesuai. Ubah pencarian atau tambahkan periode baru.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $periods->links() }}
    </div>
</x-layout>

