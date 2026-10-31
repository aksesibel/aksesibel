<x-layout :title="'Periode: '.$period->period_name">
    <div class="panel space-y-5">
        <dl class="space-y-3">
            <div><dt class="font-semibold">Nama periode</dt><dd>{{ $period->period_name }}</dd></div>
            <div><dt class="font-semibold">Tanggal mulai</dt><dd>{{ $period->start_date->format('d/m/Y') }}</dd></div>
            <div><dt class="font-semibold">Tanggal selesai</dt><dd>{{ $period->end_date->format('d/m/Y') }}</dd></div>
            <div><dt class="font-semibold">Laporan terhubung</dt><dd>{{ $period->reports_count }} laporan</dd></div>
        </dl>
        <p>Perubahan tanggal tidak memindahkan laporan yang sudah terhubung ke periode lain.</p>
        <div class="flex flex-wrap items-center gap-3">
            <a class="button" href="{{ route('manage.reporting-periods.edit', $period) }}">Edit periode</a>
            @if ($period->reports_count === 0)
                <form method="POST" action="{{ route('manage.reporting-periods.destroy', $period) }}" data-confirm="Hapus periode ini? Tindakan ini tidak dapat dibatalkan.">
                    @csrf @method('DELETE')
                    <button class="button-danger" type="submit">Hapus periode</button>
                </form>
            @else
                <p>Periode digunakan laporan dan tidak dapat dihapus.</p>
            @endif
        </div>
        <a href="{{ route('manage.reporting-periods.index') }}">Kembali ke daftar periode</a>
    </div>
</x-layout>

