@extends('layouts.app')

@section('title', 'Detail Penugasan ' . ($assignment->report?->report_number ?? ''))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header & Breadcrumb -->
    <div>
        <nav class="flex text-sm text-gray-500 mb-2" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('follow-up-assignments.index') }}" class="hover:text-gray-700">Daftar Penugasan</a>
                </li>
                <li>
                    <span class="text-gray-400">/</span>
                </li>
                <li class="font-medium text-gray-800" aria-current="page">
                    Detail Penugasan #{{ $assignment->assignment_id }}
                </li>
            </ol>
        </nav>

        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    Penugasan: {{ $assignment->report?->report_number ?? 'Laporan #' . $assignment->report_id }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Ditugaskan pada {{ $assignment->assigned_at ? $assignment->assigned_at->format('d F Y, H:i') : '-' }} WIB
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-900 uppercase tracking-wide">
                    Status: {{ $assignment->assignment_status }}
                </span>
                <a href="{{ route('follow-up-assignments.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    &larr; Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <!-- Grid Layout 2 Kolom -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri / Utama: Detail Penugasan & Catatan (2 Span) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- KARTU 1: Informasi Penugasan -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-base font-semibold text-gray-900">Informasi Penugasan Tindak Lanjut</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-5 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Petugas Penanggung Jawab</dt>
                            <dd class="mt-1 font-semibold text-gray-900 text-base">
                                {{ $assignment->assignee?->name ?? '-' }}
                            </dd>
                            <dd class="text-xs text-gray-500">{{ $assignment->assignee?->email }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pemberi Tugas (Pengelola)</dt>
                            <dd class="mt-1 font-medium text-gray-900">
                                {{ $assignment->assigner?->name ?? '-' }}
                            </dd>
                            <dd class="text-xs text-gray-500">{{ $assignment->assigner?->role?->role_name ?? 'Admin' }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Waktu Penugasan</dt>
                            <dd class="mt-1 text-gray-900">
                                {{ $assignment->assigned_at ? $assignment->assigned_at->format('d F Y, H:i') : '-' }} WIB
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tenggat Waktu (Due Date)</dt>
                            <dd class="mt-1 text-gray-900">
                                @if ($assignment->due_date)
                                    <span class="{{ $assignment->due_date->isPast() ? 'text-red-600 font-semibold' : 'font-medium' }}">
                                        {{ $assignment->due_date->format('d F Y') }}
                                        @if ($assignment->due_date->isPast())
                                            <span class="text-xs text-red-500 block font-normal">(Tenggat telah lewat)</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">Tidak ditentukan</span>
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <!-- Catatan Penugasan -->
                    <div class="mt-6 pt-5 border-t border-gray-100">
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Catatan / Instruksi Penugasan</h3>
                        @if ($assignment->assignment_notes)
                            <div class="p-4 rounded-md bg-amber-50/70 border border-amber-200 text-sm text-gray-800 whitespace-pre-line leading-relaxed">
                                {{ $assignment->assignment_notes }}
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic">Tidak ada catatan penugasan khusus.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KARTU 2: Verifikasi Relevan -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-base font-semibold text-gray-900">Riwayat Verifikasi Laporan</h2>
                </div>
                <div class="p-6">
                    @php
                        $verifications = $assignment->report?->verifications ?? collect();
                    @endphp

                    @forelse ($verifications as $verification)
                        <div class="border-l-2 border-blue-500 pl-4 py-1 space-y-2 mb-4 last:mb-0">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="font-semibold text-gray-800 text-sm">
                                    Verifikator: {{ $verification->verifier?->name ?? 'Verifikator' }}
                                </span>
                                <span>
                                    {{ $verification->verified_at ? $verification->verified_at->format('d/m/Y H:i') : '-' }}
                                </span>
                            </div>
                            <div class="text-sm">
                                <span class="text-gray-500 text-xs">Hasil Verifikasi:</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 ml-1">
                                    {{ $verification->verification_result ?? 'Valid' }}
                                </span>
                            </div>
                            @if ($verification->notes)
                                <p class="text-sm text-gray-700 bg-gray-50 p-2.5 rounded border border-gray-100 italic">
                                    "{{ $verification->notes }}"
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500 text-sm">
                            <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Laporan ini telah disetujui untuk diteruskan ke tindak lanjut.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- KARTU 3: Riwayat Progres Penanganan (PBI-14) -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Riwayat Progres Penanganan</h2>
                        <p class="text-xs text-gray-500">Catatan perkembangan tindakan lapangan dari petugas.</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $assignment->progress ? $assignment->progress->count() : 0 }} Catatan
                    </span>
                </div>
                <div class="p-6">
                    @php
                        $progressList = $assignment->progress ?? collect();
                    @endphp

                    @forelse ($progressList as $progress)
                        <div class="border-l-2 border-emerald-500 pl-4 py-2 space-y-2 mb-4 last:mb-0 bg-emerald-50/20 rounded-r-md">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center space-x-2">
                                    <span class="font-semibold text-gray-900 text-sm">
                                        {{ $progress->actor?->name ?? 'Petugas' }}
                                    </span>
                                    <span class="text-gray-400">&bull;</span>
                                    <span class="text-gray-600 font-medium">
                                        {{ $progress->actor?->role?->role_name ?? 'Petugas Tindak Lanjut' }}
                                    </span>
                                </div>
                                <span class="text-gray-500">
                                    {{ $progress->recorded_at ? $progress->recorded_at->format('d/m/Y H:i') : '-' }} WIB
                                </span>
                            </div>

                            <div class="flex items-center space-x-2 text-sm">
                                <span class="text-xs text-gray-500 uppercase font-semibold">Status:</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ in_array(strtolower($progress->progress_status), ['selesai', 'completed', 'tuntas']) ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $progress->progress_status ?? 'Dalam Penanganan' }}
                                </span>
                            </div>

                            <div class="text-sm text-gray-800 leading-relaxed bg-white p-3 rounded border border-emerald-200/50 whitespace-pre-line">
                                {{ $progress->action_description }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500 text-sm">
                            <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Belum ada catatan progres penanganan untuk penugasan ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- KARTU 4: Form Tambah Progres Penanganan (PBI-14) -->
            @php
                $canAddProgress = Auth::user()?->role?->role_name === 'Admin' || (int) Auth::id() === (int) $assignment->assigned_to;
            @endphp

            @if ($canAddProgress)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-base font-semibold text-gray-900">Tambah Progres Penanganan</h2>
                        <p class="text-xs text-gray-500">Catat tindakan yang telah atau sedang dilakukan di lapangan.</p>
                    </div>
                    <form action="{{ route('follow-up-assignments.progress.store', $assignment->assignment_id) }}" method="POST" class="p-6 space-y-4">
                        @csrf

                        <!-- Status Progres -->
                        <div>
                            <label for="progress_status" class="block text-sm font-medium text-gray-700">
                                Status Progres Penanganan <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <div class="mt-1">
                                <select id="progress_status"
                                        name="progress_status"
                                        required
                                        aria-describedby="@error('progress_status') progress_status_error @enderror"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2.5 border @error('progress_status') border-red-500 @enderror">
                                    <option value="">-- Pilih Status Progres --</option>
                                    <option value="Sedang Dikerjakan" {{ old('progress_status') === 'Sedang Dikerjakan' ? 'selected' : '' }}>
                                        Sedang Dikerjakan
                                    </option>
                                    <option value="Dalam Koordinasi Lapangan" {{ old('progress_status') === 'Dalam Koordinasi Lapangan' ? 'selected' : '' }}>
                                        Dalam Koordinasi Lapangan
                                    </option>
                                    <option value="Menunggu Material/Peralatan" {{ old('progress_status') === 'Menunggu Material/Peralatan' ? 'selected' : '' }}>
                                        Menunggu Material / Peralatan
                                    </option>
                                    <option value="Selesai" {{ old('progress_status') === 'Selesai' ? 'selected' : '' }}>
                                        Selesai Ditangani
                                    </option>
                                </select>
                            </div>
                            @error('progress_status')
                                <p id="progress_status_error" class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Uraian Tindakan -->
                        <div>
                            <label for="action_description" class="block text-sm font-medium text-gray-700">
                                Uraian Tindakan / Catatan Lapangan <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <div class="mt-1">
                                <textarea id="action_description"
                                          name="action_description"
                                          rows="4"
                                          required
                                          maxlength="2000"
                                          placeholder="Jelaskan secara rinci tindakan penanganan yang telah dilakukan, kendala yang dihadapi, atau hasil perbaikan di lapangan..."
                                          aria-describedby="action_description_help @error('action_description') action_description_error @enderror"
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2.5 border @error('action_description') border-red-500 @enderror">{{ old('action_description') }}</textarea>
                            </div>
                            <p id="action_description_help" class="mt-1 text-xs text-gray-500">
                                Maksimal 2.000 karakter. Bukti foto/dokumen akan dikelola melalui modul bukti tindak lanjut (PBI-15).
                            </p>
                            @error('action_description')
                                <p id="action_description_error" class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tombol Simpan -->
                        <div class="pt-2 flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                Simpan Catatan Progres
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Ringkasan Laporan & Fasilitas (1 Span) -->
        <div class="space-y-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-base font-semibold text-gray-900">Ringkasan Laporan</h2>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider block">Nomor Laporan</span>
                        <span class="font-bold text-gray-900 text-base">{{ $assignment->report?->report_number ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider block">Fasilitas</span>
                        <span class="font-medium text-gray-900 block">{{ $assignment->report?->facility?->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider block">Kategori Kendala</span>
                        <span class="text-gray-800 block">{{ $assignment->report?->issueCategory?->category_name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider block">Status Laporan</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                            {{ $assignment->report?->status?->status_name ?? 'Dalam Tindak Lanjut' }}
                        </span>
                    </div>

                    <div class="pt-3 border-t border-gray-100">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider block mb-1">Deskripsi Masalah</span>
                        <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded border border-gray-200/60 leading-relaxed whitespace-pre-line">
                            {{ $assignment->report?->description ?? '-' }}
                        </p>
                    </div>

                    <div class="pt-2 text-xs text-gray-400 border-t border-gray-100 italic">
                        * Data pribadi pelapor dirahasiakan sesuai ketentuan privasi pelaporan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

