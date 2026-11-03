@extends('layouts.app')

@section('title', 'Form Penugasan Tindak Lanjut')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div>
        <nav class="flex text-sm text-gray-500 mb-2" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('follow-up-assignments.index') }}" class="hover:text-gray-700">Daftar Penugasan</a>
                </li>
                <li>
                    <span class="text-gray-400">/</span>
                </li>
                <li class="font-medium text-gray-800" aria-current="page">Buat Penugasan Baru</li>
            </ol>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Form Penugasan Tindak Lanjut</h1>
        <p class="mt-1 text-sm text-gray-600">
            Tentukan petugas lapangan yang bertanggung jawab untuk menangani laporan fasilitas difabel yang telah diteruskan.
        </p>
    </div>

    <!-- Error Alert Box jika ada error validasi umum -->
    @if ($errors->any())
        <div role="alert" class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
            <div class="flex items-center space-x-2 font-semibold text-sm mb-1">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span>Mohon periksa kembali input Anda:</span>
            </div>
            <ul class="list-disc list-inside text-sm space-y-1 ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('follow-up-assignments.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- CARD 1: Ringkasan & Pemilihan Laporan -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-3">1. Informasi Laporan</h2>

            <div>
                <label for="report_id" class="block text-sm font-medium text-gray-700">
                    Pilih Laporan Siap Ditugaskan <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <div class="mt-1">
                    <select id="report_id"
                            name="report_id"
                            required
                            aria-describedby="report_id_help @error('report_id') report_id_error @enderror"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5 border @error('report_id') border-red-500 @enderror">
                        <option value="">-- Pilih Laporan --</option>
                        @foreach ($reports as $report)
                            <option value="{{ $report->report_id }}"
                                {{ (old('report_id', $selectedReport?->report_id) == $report->report_id) ? 'selected' : '' }}>
                                {{ $report->report_number }} - {{ $report->facility?->name ?? 'Fasilitas' }} ({{ $report->issueCategory?->category_name ?? 'Kendala' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <p id="report_id_help" class="mt-1 text-xs text-gray-500">
                    Hanya menampilkan laporan yang sudah terverifikasi valid dan berstatus "Dalam Tindak Lanjut".
                </p>
                @error('report_id')
                    <p id="report_id_error" class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Panel Ringkasan Laporan Terpilih jika ada -->
            @if ($selectedReport)
                <div class="mt-4 p-4 rounded-md bg-blue-50 border border-blue-100 space-y-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900">Ringkasan Laporan Terpilih</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-500 block text-xs">Nomor Laporan:</span>
                            <span class="font-semibold text-gray-900">{{ $selectedReport->report_number }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Fasilitas Terkait:</span>
                            <span class="font-semibold text-gray-900">{{ $selectedReport->facility?->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Kategori Kendala:</span>
                            <span class="font-medium text-gray-900">{{ $selectedReport->issueCategory?->category_name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Status Saat Ini:</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-200 text-blue-900">
                                {{ $selectedReport->status?->status_name ?? 'Dalam Tindak Lanjut' }}
                            </span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-blue-200/60">
                        <span class="text-gray-500 block text-xs">Deskripsi Laporan:</span>
                        <p class="text-sm text-gray-800 mt-0.5 italic">
                            "{{ $selectedReport->description }}"
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- CARD 2: Form Penugasan Petugas -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-3">2. Penentuan Petugas & Instruksi</h2>

            <!-- Pilihan Petugas -->
            <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700">
                    Petugas Penanggung Jawab <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <div class="mt-1">
                    <select id="assigned_to"
                            name="assigned_to"
                            required
                            aria-describedby="@error('assigned_to') assigned_to_error @enderror"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5 border @error('assigned_to') border-red-500 @enderror">
                        <option value="">-- Pilih Petugas Tindak Lanjut --</option>
                        @foreach ($officers as $officer)
                            <option value="{{ $officer->id }}" {{ old('assigned_to') == $officer->id ? 'selected' : '' }}>
                                {{ $officer->name }} ({{ $officer->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('assigned_to')
                    <p id="assigned_to_error" class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tenggat Waktu -->
            <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700">
                    Tenggat Waktu Penanganan <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                </label>
                <div class="mt-1">
                    <input type="date"
                           id="due_date"
                           name="due_date"
                           min="{{ date('Y-m-d') }}"
                           value="{{ old('due_date') }}"
                           aria-describedby="due_date_help @error('due_date') due_date_error @enderror"
                           class="block w-full max-w-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5 border @error('due_date') border-red-500 @enderror">
                </div>
                <p id="due_date_help" class="mt-1 text-xs text-gray-500">
                    Batas akhir penyelesaian tindak lanjut. Tanggal tidak boleh mendahului hari ini.
                </p>
                @error('due_date')
                    <p id="due_date_error" class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan Penugasan -->
            <div>
                <label for="assignment_notes" class="block text-sm font-medium text-gray-700">
                    Catatan Penugasan <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                </label>
                <div class="mt-1">
                    <textarea id="assignment_notes"
                              name="assignment_notes"
                              rows="4"
                              maxlength="2000"
                              placeholder="Tuliskan instruksi penanganan, lokasi spesifik, atau koordinasi yang perlu dilakukan petugas..."
                              aria-describedby="assignment_notes_help @error('assignment_notes') assignment_notes_error @enderror"
                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5 border @error('assignment_notes') border-red-500 @enderror">{{ old('assignment_notes') }}</textarea>
                </div>
                <p id="assignment_notes_help" class="mt-1 text-xs text-gray-500">Maksimal 2.000 karakter.</p>
                @error('assignment_notes')
                    <p id="assignment_notes_error" class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Tombol Aksi Simpan & Batal -->
        <div class="flex items-center justify-end space-x-3 pt-2">
            <a href="{{ route('follow-up-assignments.index') }}"
               class="inline-flex justify-center py-2.5 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Simpan Penugasan
            </button>
        </div>
    </form>
</div>
@endsection

