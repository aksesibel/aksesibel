@extends('layouts.follow-up')

@section('title', 'Koordinasi Tindak Lanjut')

@section('content')
<div class="page-heading">
    <div>
        <h1>Koordinasi tindak lanjut</h1>
        <p>Tugaskan petugas, pantau penanganan, dan konfirmasi hasil pekerjaan.</p>
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <x-workspace-metric label="Siap ditugaskan" :value="$counts['unassigned']" description="Laporan diteruskan dari verifikasi" icon="report" />
    <x-workspace-metric label="Dalam penanganan" :value="$counts['handling']" description="Penugasan yang masih aktif" icon="tool" />
    <x-workspace-metric label="Perlu konfirmasi" :value="$counts['review']" description="Periksa hasil yang dikirim petugas" icon="shield" />
</div>

<div class="flex flex-wrap items-end justify-between gap-3">
    <nav class="flex flex-wrap gap-2" aria-label="Tahap tindak lanjut">
        @foreach([
            'all' => 'Semua tindak lanjut',
            'unassigned' => 'Belum ditugaskan ('.$counts['unassigned'].')',
            'review' => 'Perlu konfirmasi ('.$counts['review'].')',
        ] as $key => $label)
            <a href="{{ route('follow-up-assignments.ready-reports', array_filter(['stage' => $key === 'all' ? null : $key, 'search' => $search])) }}" class="module-tab {{ $stage === $key ? 'is-active' : '' }}" @if($stage === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
    <form action="{{ route('follow-up-assignments.ready-reports') }}" method="GET" class="flex w-full gap-2 sm:max-w-xs">
        @if($stage !== 'all')<input type="hidden" name="stage" value="{{ $stage }}">@endif
        <label class="sr-only" for="follow-up-search">Cari nomor laporan atau fasilitas</label>
        <input class="field !mt-0" id="follow-up-search" type="search" name="search" value="{{ $search }}" placeholder="Cari laporan">
        <button class="button-secondary" type="submit" aria-label="Cari laporan"><x-icon name="search" :size="17" /></button>
    </form>
</div>

<div class="panel overflow-hidden !p-0">
    <div class="relative max-w-full overflow-x-auto">
        <table class="min-w-[730px]" aria-label="Daftar tindak lanjut">
            <thead>
                <tr><th scope="col">Nomor laporan</th><th scope="col">Fasilitas</th><th scope="col">Petugas</th><th scope="col">Status</th><th scope="col">Target</th><th scope="col">Tindakan</th></tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    @php
                        $active = $report->followUpAssignments->first(fn ($assignment) => in_array($assignment->assignment_status, ['assigned', 'in_progress'], true));
                        $assignment = $active ?? $report->followUpAssignments->first();
                        $needsReview = $assignment?->assignment_status === 'completed';
                        $destination = $needsReview ? 'konfirmasi-penyelesaian' : 'tugaskan-petugas';
                    @endphp
                    <tr @class(['!bg-blue-50' => $selected?->is($report)])>
                        <td class="whitespace-nowrap font-semibold text-primary">{{ $report->report_number }}</td>
                        <td class="min-w-40 font-medium">{{ $report->facility?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap text-sm text-slate-600">{{ $assignment?->assignee?->name ?? 'Belum ditugaskan' }}</td>
                        <td>
                            <span @class(['badge whitespace-nowrap', 'bg-amber-50 text-amber-900' => $needsReview, 'bg-blue-50 text-primary' => $active, 'bg-emerald-50 text-emerald-800' => ! $assignment])>
                                {{ $needsReview ? 'Menunggu konfirmasi' : ($active ? 'Dalam penanganan' : 'Siap ditugaskan') }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap text-sm text-slate-600">{{ $assignment?->due_date?->translatedFormat('j M Y') ?? 'Belum diatur' }}</td>
                        <td>
                            @if($active)
                                <a class="whitespace-nowrap text-sm font-semibold" href="{{ route('follow-up-assignments.show', $active) }}">Lihat progres →</a>
                            @else
                                <a class="whitespace-nowrap text-sm font-semibold" href="{{ route('follow-up-assignments.ready-reports', [...request()->except('selected_id'), 'selected_id' => $report->getKey()]) }}#{{ $destination }}">{{ $needsReview ? 'Tinjau hasil' : 'Tugaskan' }} →</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-sm text-slate-600">Belum ada laporan pada tahap ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
        <div class="border-t border-slate-200 px-4 py-3">{{ $reports->links() }}</div>
    @endif
</div>

<div class="grid items-start gap-5 xl:grid-cols-2">
    <section class="panel grid min-w-0 scroll-mt-5 gap-4" id="tugaskan-petugas" aria-labelledby="assign-heading">
        <h2 id="assign-heading">Tugaskan petugas</h2>
        @if($assignmentReport)
            @php
                $verification = $assignmentReport->verifications->first();
                $result = trim((string) $verification?->verification_result);
                $resultStyle = match (strtolower($result)) {
                    'valid', 'terverifikasi', 'sesuai', 'disetujui' => 'bg-green-100 text-green-800',
                    'tidak valid', 'ditolak', 'tidak sesuai' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-700',
                };
            @endphp
            <div class="grid gap-2 rounded-lg border border-slate-300 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-primary">{{ $assignmentReport->report_number }} · {{ $assignmentReport->facility?->name ?? '—' }}</p>
                <p class="text-sm leading-6 text-slate-700">{{ $assignmentReport->report_title ?: $assignmentReport->description }}</p>
                <p class="text-xs text-slate-600">Pelapor {{ $assignmentReport->user?->name ?? '—' }}</p>
                <span class="badge w-fit {{ $resultStyle }}">{{ $result !== '' ? $result : 'Hasil verifikasi belum dicatat' }}</span>
                @if($verification)
                    <p class="text-xs text-slate-600">Verifikator {{ $verification->verifier?->name ?? '—' }} · {{ $verification->verified_at?->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i') ?? '—' }}@if($verification->verified_at) WIB @endif</p>
                    @if($verification->notes)<p class="text-sm text-slate-700">{{ $verification->notes }}</p>@endif
                @endif
                @if($assignmentReport->evidences->isNotEmpty())
                    <details class="text-sm">
                        <summary class="cursor-pointer font-semibold text-primary">Bukti dari pelapor</summary>
                        @foreach($assignmentReport->evidences as $evidence)
                            <a href="{{ asset('storage/'.$evidence->file_path) }}" class="mt-2 block break-all" target="_blank" rel="noopener">{{ basename($evidence->file_path) }}</a>
                        @endforeach
                    </details>
                @endif
            </div>

            <form action="{{ route('follow-up-assignments.store') }}" method="POST" class="grid gap-4">
                @csrf
                <input type="hidden" name="report_id" value="{{ $assignmentReport->getKey() }}">
                <div>
                    <label for="assigned_to">Petugas penanganan *</label>
                    <select class="field" id="assigned_to" name="assigned_to" required @disabled($officers->isEmpty())>
                        <option value="">{{ $officers->isEmpty() ? 'Belum ada petugas aktif' : 'Pilih petugas' }}</option>
                        @foreach($officers as $officer)
                            <option value="{{ $officer->getKey() }}" @selected((string) old('assigned_to') === (string) $officer->getKey())>{{ $officer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="due_date">Target penyelesaian</label>
                    <input class="field" type="date" id="due_date" name="due_date" min="{{ today()->toDateString() }}" value="{{ old('due_date') }}">
                </div>
                <div>
                    <label for="assignment_notes">Instruksi penanganan</label>
                    <textarea class="field" id="assignment_notes" name="assignment_notes" rows="4" maxlength="2000" placeholder="Tuliskan tindakan yang perlu dilakukan">{{ old('assignment_notes') }}</textarea>
                </div>
                <div class="information-note"><x-icon name="info" :size="18" /><div><strong class="block text-primary">Penugasan akan muncul di Tugas saya</strong><p>Petugas yang dipilih bisa membuka laporan dan mencatat progres.</p></div></div>
                <button class="button w-fit" type="submit" @disabled($officers->isEmpty())>Simpan Penugasan</button>
            </form>
        @else
            <p class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600">Belum ada laporan yang siap ditugaskan.</p>
        @endif
    </section>

    <section class="panel grid min-w-0 scroll-mt-5 gap-4" id="konfirmasi-penyelesaian" aria-labelledby="review-heading">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="review-heading">Konfirmasi penyelesaian</h2>
            @if($reviewAssignment)<span class="badge bg-amber-50 text-amber-900">Menunggu konfirmasi</span>@endif
        </div>
        @if($reviewAssignment)
            @php
                $progress = $reviewAssignment->progress->sortByDesc('recorded_at');
                $lastUpdate = $progress->first();
                $evidence = $progress->flatMap(fn ($update) => $update->evidences)->first();
            @endphp
            <div class="grid gap-2 rounded-lg border border-slate-300 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-primary">{{ $reviewReport->report_number }} · {{ $reviewReport->facility?->name ?? '—' }}</p>
                <p class="text-sm text-slate-700">{{ $reviewReport->report_title ?: $reviewReport->description }}</p>
            </div>
            @if($evidence)
                <figure class="grid gap-2">
                    <a class="block" href="{{ route('evidence.show', $evidence) }}" target="_blank" rel="noopener">
                        <img src="{{ route('evidence.show', $evidence) }}" alt="Bukti hasil {{ $reviewReport->report_number }}" class="aspect-video w-full rounded-lg object-cover" loading="lazy">
                    </a>
                    <figcaption class="text-xs text-slate-600">Bukti hasil · {{ $reviewAssignment->assignee?->name ?? 'Petugas' }}</figcaption>
                </figure>
            @else
                <p class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600">Belum ada foto bukti hasil penanganan.</p>
            @endif
            @if($lastUpdate?->action_description)<p class="whitespace-pre-line text-sm leading-6 text-slate-700">{{ $lastUpdate->action_description }}</p>@endif
            <a class="text-sm font-semibold text-primary hover:underline" href="{{ route('follow-up-assignments.show', $reviewAssignment) }}">Lihat riwayat tugas →</a>
            @if($completedStatus)
                <form action="{{ route('verifikasi.status.update', $reviewReport) }}" method="POST" class="grid gap-4">
                    @csrf
                    <input type="hidden" name="status_id" value="{{ $completedStatus }}">
                    <label class="flex items-center gap-3 text-sm font-normal">
                        <input type="checkbox" required class="size-5 shrink-0 accent-blue-700">
                        Saya telah meninjau hasil penanganan.
                    </label>
                    <div>
                        <label for="confirmation_notes">Catatan konfirmasi</label>
                        <textarea class="field" id="confirmation_notes" name="notes" rows="3" maxlength="2000" placeholder="Catat hasil peninjauan">{{ old('notes') }}</textarea>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-4 text-sm text-emerald-900">Konfirmasi akan mengubah status laporan menjadi Selesai.</div>
                    <button class="button w-fit" type="submit">Konfirmasi penyelesaian</button>
                </form>
            @else
                <p class="rounded-lg bg-blue-50 p-4 text-sm text-slate-700">Admin dapat menyimpan konfirmasi penyelesaian laporan.</p>
            @endif
        @else
            <p class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600">Belum ada hasil penanganan yang perlu dikonfirmasi.</p>
        @endif
    </section>
</div>
@endsection
