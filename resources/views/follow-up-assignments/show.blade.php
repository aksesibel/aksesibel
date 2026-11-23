@extends('layouts.follow-up')

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

                            {{-- Bukti foto yang diunggah untuk entri progres ini (PBI-15) --}}
                            @if ($progress->evidences && $progress->evidences->count() > 0)
                                <div class="mt-2 pt-2 border-t border-emerald-200/40">
                                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">
                                        <svg class="inline-block w-3.5 h-3.5 mr-0.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Bukti Foto ({{ $progress->evidences->count() }})
                                    </p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @foreach ($progress->evidences as $evidence)
                                            <a href="{{ route('evidence.show', $evidence->follow_up_evidence_id) }}"
                                               target="_blank"
                                               class="block group rounded-md overflow-hidden border border-gray-200 hover:border-blue-400 transition-colors bg-white">
                                                <img src="{{ route('evidence.show', $evidence->follow_up_evidence_id) }}"
                                                     alt="Bukti #{{ $evidence->follow_up_evidence_id }}"
                                                     class="w-full h-24 object-cover group-hover:opacity-90 transition-opacity"
                                                     loading="lazy">
                                                <div class="px-2 py-1 text-xs text-gray-500 truncate">
                                                    {{ $evidence->uploaded_at ? $evidence->uploaded_at->format('d/m/Y H:i') : '-' }}
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                    @if ($progress->evidences->first()?->description)
                                        <p class="text-xs text-gray-500 italic mt-1">{{ $progress->evidences->first()->description }}</p>
                                    @endif
                                </div>
                            @endif
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
                $userRole = Auth::user()?->role?->role_name;
                $isManager = in_array($userRole, ['Admin', 'Pengelola Fasilitas'], true);
                $isAssignedOfficer = (int) Auth::id() === (int) $assignment->assigned_to && $userRole === 'Petugas Tindak Lanjut';
                $isAssignmentActive = ! in_array(strtolower((string) $assignment->assignment_status), ['cancelled', 'dibatalkan'], true);
                $isReportForwarded = $assignment->report?->status?->status_name === 'Dalam Tindak Lanjut';
                $canAddProgress = ($isManager || $isAssignedOfficer) && Auth::user()?->account_status === 'active' && $isAssignmentActive && $isReportForwarded;
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
                                    @foreach (\App\Models\FollowUpProgress::STATUS_OPTIONS as $option)
                                        <option value="{{ $option }}" {{ old('progress_status') === $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
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

            {{-- KARTU 5: Unggah Bukti Foto Progres (PBI-15) --}}
            @if ($canAddProgress)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-base font-semibold text-gray-900">Unggah Foto Bukti Progres</h2>
                        <p class="text-xs text-gray-500">Lampirkan dokumentasi penanganan pada entri progres yang sudah tersimpan.</p>
                    </div>

                    @if ($progressList->isEmpty())
                        <div class="p-6 text-center text-sm text-gray-500">
                            <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Simpan progres terlebih dahulu sebelum mengunggah bukti.
                        </div>
                    @else
                        <form action="{{ route('follow-up-assignments.evidence.store', $assignment->assignment_id) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              class="p-6 space-y-4">
                            @csrf

                            {{-- Pilih Entri Progres --}}
                            <div>
                                <label for="progress_id" class="block text-sm font-medium text-gray-700">
                                    Progres Tujuan <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <div class="mt-1">
                                    <select id="progress_id"
                                            name="progress_id"
                                            required
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5 border @error('progress_id') border-red-500 @enderror">
                                        <option value="">-- Pilih Entri Progres --</option>
                                        @foreach ($progressList as $p)
                                            <option value="{{ $p->progress_id }}" {{ old('progress_id') == $p->progress_id ? 'selected' : '' }}>
                                                {{ $p->recorded_at ? $p->recorded_at->format('d/m/Y H:i') : '-' }}
                                                — {{ Str::limit($p->action_description, 50) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('progress_id')
                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Area Upload Foto --}}
                            <div>
                                <label for="photos" class="block text-sm font-medium text-gray-700 mb-1">
                                    Foto Bukti <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <div class="relative border-2 border-dashed border-blue-300 bg-blue-50/60 rounded-lg p-6 text-center cursor-pointer hover:bg-blue-50 transition-colors"
                                     onclick="document.getElementById('photos').click()">
                                    <svg class="mx-auto h-10 w-10 text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-blue-600">Unggah foto progres / hasil</p>
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG • Maks. 5 MB per foto • Hingga 5 foto</p>
                                </div>
                                <input type="file"
                                       id="photos"
                                       name="photos[]"
                                       multiple
                                       accept="image/jpeg,image/png"
                                       class="sr-only"
                                       aria-label="Pilih foto bukti"
                                       onchange="updateFileList(this)">
                                <div id="file-list" class="mt-2 text-xs text-gray-500"></div>
                                @error('photos')
                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                                @error('photos.*')
                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Deskripsi Opsional --}}
                            <div>
                                <label for="evidence_description" class="block text-sm font-medium text-gray-700">
                                    Deskripsi Bukti <span class="text-gray-400 font-normal">(opsional)</span>
                                </label>
                                <div class="mt-1">
                                    <textarea id="evidence_description"
                                              name="description"
                                              rows="2"
                                              maxlength="500"
                                              placeholder="Keterangan singkat mengenai bukti foto..."
                                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5 border @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                                </div>
                                @error('description')
                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tombol Unggah --}}
                            <div class="pt-2 flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center px-5 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Unggah Bukti
                                </button>
                            </div>
                        </form>
                    @endif
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

@push('scripts')
<script>
    function updateFileList(input) {
        const container = document.getElementById('file-list');
        if (!input.files.length) {
            container.textContent = 'Belum ada foto baru dipilih.';
            return;
        }
        const names = Array.from(input.files).map(f => f.name);
        container.textContent = names.length + ' foto dipilih: ' + names.join(', ');
    }
</script>
@endpush
@endsection

