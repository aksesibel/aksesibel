@php
    $isEdit  = isset($facility);
    $title   = $isEdit ? 'Ubah Fasilitas' : 'Tambah Fasilitas';
    $action  = $isEdit
        ? route('admin.facilities.update', $facility->facility_id)
        : route('admin.facilities.store');
    $method  = $isEdit ? 'PUT' : 'POST';

    $days = [
        'senin'  => 'Senin',
        'selasa' => 'Selasa',
        'rabu'   => 'Rabu',
        'kamis'  => 'Kamis',
        'jumat'  => 'Jumat',
        'sabtu'  => 'Sabtu',
        'minggu' => 'Minggu',
    ];

    $existingHours = $isEdit && $facility->operating_hours
        ? json_decode($facility->operating_hours, true)
        : [];
@endphp

<x-layout :title="$title">

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

        {{-- Breadcrumb --}}
        <nav aria-label="Lokasi halaman" class="mb-6">
            <ol class="flex items-center gap-2 text-sm text-[#475569]">
                <li><a href="{{ route('admin.facilities.index') }}" class="hover:text-[#1D4ED8] transition-colors">Kelola Fasilitas</a></li>
                <li aria-hidden="true"><span class="mx-1 text-[#CBD5E1]">/</span></li>
                <li aria-current="page" class="text-[#0F172A] font-medium">{{ $title }}</li>
            </ol>
        </nav>

        <h1 class="text-2xl font-extrabold text-[#0F172A] mb-8">{{ $title }}</h1>

        <form action="{{ $action }}" method="POST" novalidate aria-label="{{ $title }}">
            @csrf
            @if($method === 'PUT') @method('PUT') @endif

            {{-- Pesan error global --}}
            @if($errors->any())
                <div role="alert" aria-live="assertive" class="mb-6 bg-[#FEE2E2] border border-red-200 text-[#B91C1C] rounded-xl p-4 text-sm">
                    <p class="font-semibold mb-1">Mohon perbaiki kesalahan berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white border border-[#E2E8F0] rounded-xl p-6 space-y-6">

                {{-- Nama --}}
                <div>
                    <label for="name" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                        Nama Fasilitas <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                    </label>
                    <input id="name" type="text" name="name"
                           value="{{ old('name', $facility->name ?? '') }}"
                           required minlength="3" maxlength="120"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
                           class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('name') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                           placeholder="Contoh: Perpustakaan Nasional RI">
                    @error('name')
                        <p id="name-error" role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C] flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="description" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                        Deskripsi <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                    </label>
                    <textarea id="description" name="description"
                              required maxlength="1000" rows="4"
                              aria-required="true"
                              aria-describedby="desc-hint {{ $errors->has('description') ? 'desc-error' : '' }}"
                              class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('description') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] resize-y"
                              placeholder="Deskripsikan fasilitas, kondisi aksesibilitas, dan informasi penting lainnya…">{{ old('description', $facility->description ?? '') }}</textarea>
                    <p id="desc-hint" class="mt-1 text-xs text-[#94A3B8]">Maksimal 1.000 karakter.</p>
                    @error('description')
                        <p id="desc-error" role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C] flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                        Kategori <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                    </label>
                    <select id="category_id" name="category_id"
                            required
                            aria-required="true"
                            class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('category_id') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->category_id }}"
                                {{ old('category_id', $facility->category_id ?? '') == $cat->category_id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Alamat & Kota --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                            Alamat <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                        </label>
                        <input id="address" type="text" name="address"
                               value="{{ old('address', $facility->address ?? '') }}"
                               required
                               aria-required="true"
                               class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('address') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                               placeholder="Contoh: Jl. Medan Merdeka Selatan No.11">
                        @error('address')
                            <p role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                            Kota <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                        </label>
                        <input id="city" type="text" name="city"
                               value="{{ old('city', $facility->city ?? '') }}"
                               required
                               aria-required="true"
                               class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('city') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                               placeholder="Contoh: Jakarta">
                        @error('city')
                            <p role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Status Aksesibilitas --}}
                <div>
                    <label for="accessibility_status" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                        Status Aksesibilitas <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                    </label>
                    <select id="accessibility_status" name="accessibility_status"
                            required
                            aria-required="true"
                            class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('accessibility_status') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}"
                                {{ old('accessibility_status', $facility->accessibility_status ?? 'MENUNGGU_KONFIRMASI') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('accessibility_status')
                        <p role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kontak --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                            Nomor Telepon <span class="text-[#94A3B8] font-normal">(opsional)</span>
                        </label>
                        <input id="phone" type="tel" name="phone"
                               value="{{ old('phone', $facility->phone ?? '') }}"
                               class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('phone') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                               placeholder="08123456789">
                        @error('phone')
                            <p role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                            Email <span class="text-[#94A3B8] font-normal">(opsional)</span>
                        </label>
                        <input id="email" type="email" name="email"
                               value="{{ old('email', $facility->email ?? '') }}"
                               class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('email') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                               placeholder="info@fasilitas.go.id">
                        @error('email')
                            <p role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Jam Operasional --}}
                <fieldset>
                    <legend class="text-sm font-semibold text-[#0F172A] mb-3">
                        Jam Operasional <span class="text-[#94A3B8] font-normal">(opsional)</span>
                    </legend>
                    <div class="space-y-3">
                        @foreach($days as $dayKey => $dayLabel)
                            @php
                                $dayData = old("operating_hours.{$dayKey}", $existingHours[$dayKey] ?? null);
                                $isClosed = $dayData && isset($dayData['closed']);
                                $is24     = $dayData && isset($dayData['buka_24_jam']);
                                $openVal  = $dayData['open']  ?? '';
                                $closeVal = $dayData['close'] ?? '';
                            @endphp
                            <div x-data="{ mode: '{{ $isClosed ? 'tutup' : ($is24 ? '24jam' : 'custom') }}' }"
                                 class="flex flex-wrap items-center gap-3 py-2 border-b border-[#F1F5F9] last:border-0">
                                <span class="w-16 text-sm font-medium text-[#0F172A] shrink-0">{{ $dayLabel }}</span>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <label class="flex items-center gap-1.5 cursor-pointer text-sm text-[#475569]">
                                        <input type="radio" name="operating_hours[{{ $dayKey }}][type]"
                                               value="custom" x-model="mode"
                                               class="w-4 h-4 text-[#1D4ED8] focus:ring-[#1D4ED8]">
                                        Buka
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer text-sm text-[#475569]">
                                        <input type="radio" name="operating_hours[{{ $dayKey }}][type]"
                                               value="24jam" x-model="mode"
                                               class="w-4 h-4 text-[#1D4ED8] focus:ring-[#1D4ED8]">
                                        24 Jam
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer text-sm text-[#475569]">
                                        <input type="radio" name="operating_hours[{{ $dayKey }}][type]"
                                               value="tutup" x-model="mode"
                                               class="w-4 h-4 text-[#1D4ED8] focus:ring-[#1D4ED8]">
                                        Tutup
                                    </label>
                                </div>

                                <div x-show="mode === 'custom'" class="flex items-center gap-2">
                                    <label :for="'open_{{ $dayKey }}'" class="sr-only">Jam buka {{ $dayLabel }}</label>
                                    <input id="open_{{ $dayKey }}" type="time"
                                           name="operating_hours[{{ $dayKey }}][open]"
                                           value="{{ $openVal }}"
                                           class="px-2 py-1.5 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[36px]">
                                    <span class="text-[#475569] text-sm" aria-hidden="true">–</span>
                                    <label :for="'close_{{ $dayKey }}'" class="sr-only">Jam tutup {{ $dayLabel }}</label>
                                    <input id="close_{{ $dayKey }}" type="time"
                                           name="operating_hours[{{ $dayKey }}][close]"
                                           value="{{ $closeVal }}"
                                           class="px-2 py-1.5 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[36px]">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

            </div>{{-- end card --}}

            {{-- Tombol aksi --}}
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-8">
                <a href="{{ route('admin.facilities.index') }}"
                   class="px-5 py-2.5 rounded-lg border border-[#E2E8F0] text-[#475569] text-sm font-medium hover:bg-[#F8FAFC] transition-colors min-h-[44px] flex items-center justify-center">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-sm font-semibold transition-colors min-h-[44px] flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                    {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Fasilitas' }}
                </button>
            </div>

        </form>
    </div>

</x-layout>
