@php
    $isEdit  = isset($facility);
    $title   = $isEdit ? 'Ubah Fasilitas' : 'Tambah Fasilitas';
    $action  = $isEdit ? route('admin.facilities.update', $facility->facility_id) : route('admin.facilities.store');
    $method  = $isEdit ? 'PUT' : 'POST';

    $days = ['senin'=>'Senin','selasa'=>'Selasa','rabu'=>'Rabu','kamis'=>'Kamis',
             'jumat'=>'Jumat','sabtu'=>'Sabtu','minggu'=>'Minggu'];

    $existingHours = $isEdit && $facility->operating_hours
        ? json_decode($facility->operating_hours, true) : [];
@endphp

<x-layout :title="$title">

{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">

    <nav aria-label="Lokasi halaman" class="mb-6">
        <ol class="flex items-center gap-2 text-sm text-[#475569]">
            <li><a href="{{ route('admin.facilities.index') }}" class="hover:text-[#1D4ED8] transition-colors">Kelola Fasilitas</a></li>
            <li aria-hidden="true"><span class="mx-1 text-[#CBD5E1]">/</span></li>
            <li aria-current="page" class="text-[#0F172A] font-medium">{{ $title }}</li>
        </ol>
    </nav>

    <h1 class="text-2xl font-extrabold text-[#0F172A] mb-8">{{ $title }}</h1>

    {{-- Peringatan koordinat --}}
    @if(session('coord_warning'))
        <div role="alert" class="mb-6 bg-[#FEF3C7] border border-amber-200 text-[#B45309] rounded-xl p-4 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            {{ session('coord_warning') }}
        </div>
    @endif

    <form action="{{ $action }}" method="POST" novalidate
          x-data="facilityForm()"
          x-init="init()"
          aria-label="{{ $title }}">
        @csrf
        @if($method === 'PUT') @method('PUT') @endif

        @if($errors->any())
            <div role="alert" aria-live="assertive" class="mb-6 bg-[#FEE2E2] border border-red-200 text-[#B91C1C] rounded-xl p-4 text-sm">
                <p class="font-semibold mb-1">Mohon perbaiki kesalahan berikut:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- ==================== INFORMASI DASAR ==================== --}}
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-6 space-y-6 mb-6">
            <h2 class="font-bold text-[#0F172A] text-base border-b border-[#F1F5F9] pb-3">Informasi Dasar</h2>

            <div>
                <label for="name" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Nama Fasilitas <span class="text-[#B91C1C]" aria-hidden="true">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', $facility->name ?? '') }}"
                       required minlength="3" maxlength="120"
                       class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('name') ? 'border-[#B91C1C]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                       placeholder="Contoh: Perpustakaan Nasional RI">
                @error('name')<p role="alert" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Deskripsi <span class="text-[#B91C1C]" aria-hidden="true">*</span></label>
                <textarea id="description" name="description" required maxlength="1000" rows="4"
                          class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('description') ? 'border-[#B91C1C]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] resize-y"
                          placeholder="Deskripsikan fasilitas…">{{ old('description', $facility->description ?? '') }}</textarea>
                @error('description')<p role="alert" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="category_id" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Kategori <span class="text-[#B91C1C]" aria-hidden="true">*</span></label>
                <select id="category_id" name="category_id" required
                        class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('category_id') ? 'border-[#B91C1C]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->category_id }}" {{ old('category_id', $facility->category_id ?? '') == $cat->category_id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p role="alert" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="accessibility_status" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Status Aksesibilitas <span class="text-[#B91C1C]" aria-hidden="true">*</span></label>
                <select id="accessibility_status" name="accessibility_status" required
                        class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('accessibility_status') ? 'border-[#B91C1C]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('accessibility_status', $facility->accessibility_status ?? 'MENUNGGU_KONFIRMASI') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Nomor Telepon <span class="text-[#94A3B8] font-normal">(opsional)</span></label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', $facility->phone ?? '') }}"
                           class="w-full px-3 py-2.5 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]" placeholder="08123456789">
                    @error('phone')<p role="alert" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Email <span class="text-[#94A3B8] font-normal">(opsional)</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email', $facility->email ?? '') }}"
                           class="w-full px-3 py-2.5 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]" placeholder="info@fasilitas.go.id">
                    @error('email')<p role="alert" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ==================== LOKASI ==================== --}}
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-6 space-y-6 mb-6">
            <h2 class="font-bold text-[#0F172A] text-base border-b border-[#F1F5F9] pb-3">Lokasi</h2>

            {{-- Alamat jalan --}}
            <div>
                <label for="address" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Alamat Jalan <span class="text-[#B91C1C]" aria-hidden="true">*</span></label>
                <input id="address" type="text" name="address" value="{{ old('address', $facility->address ?? '') }}"
                       required
                       class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('address') ? 'border-[#B91C1C]' : 'border-[#E2E8F0]' }} text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                       placeholder="Jl. Medan Merdeka Selatan No.11, RT 1/RW 1">
                @error('address')<p role="alert" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>@enderror
            </div>

            {{-- Kode Pos --}}
            <div class="w-36">
                <label for="postal_code" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Kode Pos</label>
                <input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code', $facility->postal_code ?? '') }}"
                       maxlength="5" pattern="\d{5}"
                       class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('postal_code') ? 'border-[#B91C1C]' : 'border-[#E2E8F0]' }} text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                       placeholder="10110">
                @error('postal_code')<p role="alert" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>@enderror
            </div>

            {{-- Dropdown Wilayah Berjenjang --}}
            <fieldset class="space-y-4">
                <legend class="text-sm font-semibold text-[#0F172A]">Wilayah Administratif</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Provinsi --}}
                    <div>
                        <label for="province_id" class="block text-xs font-medium text-[#475569] mb-1">Provinsi</label>
                        <select id="province_id" name="province_id"
                                x-model="provinceId"
                                @change="onProvinceChange()"
                                class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                            <option value="">-- Pilih Provinsi --</option>
                            @foreach($provinces as $prov)
                                <option value="{{ $prov->region_id }}" data-code="{{ $prov->code }}" {{ old('province_id', $facility->province_id ?? '') == $prov->region_id ? 'selected' : '' }}>{{ $prov->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kota/Kabupaten --}}
                    <div>
                        <label for="city_region_id" class="block text-xs font-medium text-[#475569] mb-1">Kota / Kabupaten</label>
                        <select id="city_region_id" name="city_region_id"
                                x-model="cityId"
                                @change="onCityChange()"
                                :disabled="!provinceId"
                                class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px] disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">-- Pilih Kota/Kab --</option>
                            <template x-for="c in cities" :key="c.region_id">
                                <option :value="c.region_id" :selected="c.region_id == {{ old('city_region_id', $facility->city_region_id ?? 'null') }}" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Kecamatan --}}
                    <div>
                        <label for="district_id" class="block text-xs font-medium text-[#475569] mb-1">Kecamatan</label>
                        <select id="district_id" name="district_id"
                                x-model="districtId"
                                @change="onDistrictChange()"
                                :disabled="!cityId"
                                class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px] disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">-- Pilih Kecamatan --</option>
                            <template x-for="d in districts" :key="d.region_id">
                                <option :value="d.region_id" :selected="d.region_id == {{ old('district_id', $facility->district_id ?? 'null') }}" x-text="d.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Kelurahan --}}
                    <div>
                        <label for="region_id" class="block text-xs font-medium text-[#475569] mb-1">Kelurahan / Desa</label>
                        <select id="region_id" name="region_id"
                                x-model="villageId"
                                :disabled="!districtId"
                                class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px] disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">-- Pilih Kelurahan --</option>
                            <template x-for="v in villages" :key="v.region_id">
                                <option :value="v.region_id" :selected="v.region_id == {{ old('region_id', $facility->region_id ?? 'null') }}" x-text="v.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </fieldset>

            {{-- Pemilih Koordinat --}}
            <fieldset class="space-y-4">
                <legend class="text-sm font-semibold text-[#0F172A]">Koordinat Lokasi <span class="text-[#94A3B8] font-normal">(opsional)</span></legend>

                {{-- Input lat/lng --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="block text-xs font-medium text-[#475569] mb-1">Latitude</label>
                        <input id="latitude" type="number" name="latitude" step="any"
                               x-model="lat"
                               @input.debounce.500ms="updateMarker()"
                               value="{{ old('latitude', $facility->latitude ?? '') }}"
                               class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                               placeholder="-6.2000">
                        @error('latitude')<p role="alert" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="longitude" class="block text-xs font-medium text-[#475569] mb-1">Longitude</label>
                        <input id="longitude" type="number" name="longitude" step="any"
                               x-model="lng"
                               @input.debounce.500ms="updateMarker()"
                               value="{{ old('longitude', $facility->longitude ?? '') }}"
                               class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                               placeholder="106.8000">
                        @error('longitude')<p role="alert" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Alternatif tanpa mouse: geser titik dengan tombol arah --}}
                <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg p-3 space-y-2">
                    <p class="text-xs font-medium text-[#475569]">Geser titik tanpa mouse <span class="text-[#94A3B8] font-normal">(gunakan tombol arah)</span></p>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="inline-grid grid-cols-3 gap-1" role="group" aria-label="Geser titik koordinat">
                            <span aria-hidden="true"></span>
                            <button type="button" @click="nudge('north')" :disabled="!lat || !lng"
                                    class="w-11 h-11 flex items-center justify-center rounded-lg border border-[#E2E8F0] bg-white text-[#475569] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                    aria-label="Geser ke utara">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                            </button>
                            <span aria-hidden="true"></span>
                            <button type="button" @click="nudge('west')" :disabled="!lat || !lng"
                                    class="w-11 h-11 flex items-center justify-center rounded-lg border border-[#E2E8F0] bg-white text-[#475569] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                    aria-label="Geser ke barat">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l-7 7 7 7"/></svg>
                            </button>
                            <button type="button" @click="nudge('south')" :disabled="!lat || !lng"
                                    class="w-11 h-11 flex items-center justify-center rounded-lg border border-[#E2E8F0] bg-white text-[#475569] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                    aria-label="Geser ke selatan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                            </button>
                            <button type="button" @click="nudge('east')" :disabled="!lat || !lng"
                                    class="w-11 h-11 flex items-center justify-center rounded-lg border border-[#E2E8F0] bg-white text-[#475569] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                    aria-label="Geser ke timur">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M12 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <label for="nudge_step" class="text-xs text-[#475569]">Langkah</label>
                            <select id="nudge_step" x-model.number="nudgeStep"
                                    class="px-2 py-2 rounded-lg border border-[#E2E8F0] text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                                <option value="0.0001">0,0001° (± 11 m)</option>
                                <option value="0.001">0,001° (± 111 m)</option>
                                <option value="0.01">0,01° (± 1,1 km)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Tombol aksi koordinat --}}
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="geocodeAddress()"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-[#E2E8F0] text-xs font-medium text-[#475569] hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        Cari alamat di peta
                    </button>
                    <button type="button" @click="useMyLocation()"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-[#E2E8F0] text-xs font-medium text-[#475569] hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
                        Gunakan lokasi saya
                    </button>
                    <button type="button" @click="clearCoords()" x-show="lat || lng"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-[#FEE2E2] text-xs font-medium text-[#B91C1C] hover:bg-[#FEF2F2] transition-colors min-h-[44px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        Hapus titik
                    </button>
                    <input type="hidden" name="coordinate_source" x-model="coordSource">
                </div>

                {{-- Status geocode --}}
                <div x-show="geocodeStatus" aria-live="polite" class="text-xs text-[#475569]" x-text="geocodeStatus"></div>

                {{-- Peringatan di luar Indonesia --}}
                <div x-show="coordWarning" role="alert" class="text-xs text-[#B45309] bg-[#FEF3C7] border border-amber-200 rounded-lg px-3 py-2 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                    Koordinat di luar wilayah Indonesia. Pastikan sudah benar.
                </div>

                {{-- Peringatan jauh dari wilayah provinsi yang dipilih --}}
                <div x-show="regionWarning" role="alert" class="text-xs text-[#B45309] bg-[#FEF3C7] border border-amber-200 rounded-lg px-3 py-2 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                    Titik koordinat tampak jauh dari wilayah provinsi yang dipilih. Periksa kembali, atau abaikan bila memang sudah tepat.
                </div>

                {{-- Sumber & akurasi koordinat --}}
                <div x-show="lat && lng" class="text-xs text-[#475569] bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg px-3 py-2 space-y-0.5">
                    <p>Sumber koordinat: <strong x-text="sourceLabel"></strong></p>
                    <p class="text-[#94A3B8]" x-text="accuracyText"></p>
                    <p class="text-[#94A3B8]" x-text="'Titik: ' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5)"></p>
                </div>

                {{-- Peta Leaflet --}}
                <div class="relative">
                    <a href="#after-map" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-10 focus:bg-white focus:px-3 focus:py-1 focus:rounded focus:text-sm focus:text-[#1D4ED8]">
                        Lewati peta
                    </a>
                    <div id="facility-map"
                         class="w-full rounded-xl border border-[#E2E8F0] overflow-hidden"
                         style="height: 320px;"
                         aria-label="Peta pemilihan titik lokasi fasilitas. Klik untuk menentukan koordinat."
                         role="application">
                    </div>
                    <p class="text-[0.65rem] text-[#94A3B8] mt-1 text-right">
                        © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" class="underline">OpenStreetMap contributors</a>
                    </p>
                </div>
                <div id="after-map"></div>
            </fieldset>

            {{-- Catatan Lokasi --}}
            <div>
                <label for="location_note" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Catatan Lokasi <span class="text-[#94A3B8] font-normal">(opsional)</span></label>
                <textarea id="location_note" name="location_note" rows="2" maxlength="300"
                          class="w-full px-3 py-2.5 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] resize-y"
                          placeholder="Patokan atau keterangan tambahan, mis. 'Seberang Halte Bundaran HI'…">{{ old('location_note', $facility->location_note ?? '') }}</textarea>
            </div>
            <div>
                <label for="entrance_note" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Pintu Masuk Aksesibel <span class="text-[#94A3B8] font-normal">(opsional)</span></label>
                <textarea id="entrance_note" name="entrance_note" rows="2" maxlength="300"
                          class="w-full px-3 py-2.5 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] resize-y"
                          placeholder="Keterangan pintu masuk yang dapat diakses, mis. 'Pintu belakang dengan ramp, buka pukul 07:00'…">{{ old('entrance_note', $facility->entrance_note ?? '') }}</textarea>
            </div>
            <div>
                <label for="nearby_transit" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Transportasi Terdekat <span class="text-[#94A3B8] font-normal">(opsional)</span></label>
                <input id="nearby_transit" type="text" name="nearby_transit"
                       value="{{ old('nearby_transit', $facility->nearby_transit ?? '') }}"
                       maxlength="300"
                       class="w-full px-3 py-2.5 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                       placeholder="Halte TransJakarta Blok M 100m, Stasiun MRT Blok M 200m">
            </div>
        </div>

        {{-- ==================== JAM OPERASIONAL ==================== --}}
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-6 mb-6">
            <h2 class="font-bold text-[#0F172A] text-base border-b border-[#F1F5F9] pb-3 mb-4">Jam Operasional <span class="text-[#94A3B8] font-normal text-sm">(opsional)</span></h2>
            <div class="space-y-3">
                @foreach($days as $dayKey => $dayLabel)
                    @php
                        $dayData  = old("operating_hours.{$dayKey}", $existingHours[$dayKey] ?? null);
                        $isClosed = $dayData && isset($dayData['closed']);
                        $is24     = $dayData && isset($dayData['buka_24_jam']);
                        $openVal  = $dayData['open']  ?? '';
                        $closeVal = $dayData['close'] ?? '';
                    @endphp
                    <div x-data="{ mode: '{{ $isClosed ? 'tutup' : ($is24 ? '24jam' : 'custom') }}' }"
                         class="flex flex-wrap items-center gap-3 py-2 border-b border-[#F1F5F9] last:border-0">
                        <span class="w-16 text-sm font-medium text-[#0F172A] shrink-0">{{ $dayLabel }}</span>
                        <div class="flex items-center gap-2 flex-wrap">
                            <label class="flex items-center gap-1.5 cursor-pointer text-sm text-[#475569]"><input type="radio" name="operating_hours[{{ $dayKey }}][type]" value="custom" x-model="mode" class="w-4 h-4 text-[#1D4ED8]"> Buka</label>
                            <label class="flex items-center gap-1.5 cursor-pointer text-sm text-[#475569]"><input type="radio" name="operating_hours[{{ $dayKey }}][type]" value="24jam"  x-model="mode" class="w-4 h-4 text-[#1D4ED8]"> 24 Jam</label>
                            <label class="flex items-center gap-1.5 cursor-pointer text-sm text-[#475569]"><input type="radio" name="operating_hours[{{ $dayKey }}][type]" value="tutup"  x-model="mode" class="w-4 h-4 text-[#1D4ED8]"> Tutup</label>
                        </div>
                        <div x-show="mode === 'custom'" class="flex items-center gap-2">
                            <label :for="'open_{{ $dayKey }}'" class="sr-only">Jam buka {{ $dayLabel }}</label>
                            <input id="open_{{ $dayKey }}" type="time" name="operating_hours[{{ $dayKey }}][open]" value="{{ $openVal }}" class="px-2 py-1.5 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]">
                            <span aria-hidden="true">–</span>
                            <label :for="'close_{{ $dayKey }}'" class="sr-only">Jam tutup {{ $dayLabel }}</label>
                            <input id="close_{{ $dayKey }}" type="time" name="operating_hours[{{ $dayKey }}][close]" value="{{ $closeVal }}" class="px-2 py-1.5 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
            <a href="{{ route('admin.facilities.index') }}"
               class="px-5 py-2.5 rounded-lg border border-[#E2E8F0] text-[#475569] text-sm font-medium hover:bg-[#F8FAFC] transition-colors min-h-[44px] flex items-center justify-center">Batal</a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-sm font-semibold transition-colors min-h-[44px] flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Fasilitas' }}
            </button>
        </div>
    </form>
</div>

{{-- Leaflet JS + Alpine logic --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN2GKDk=" crossorigin=""></script>

<script>
function facilityForm() {
    return {
        lat:          '{{ old('latitude',  $facility->latitude  ?? '') }}',
        lng:          '{{ old('longitude', $facility->longitude ?? '') }}',
        coordSource:  '{{ old('coordinate_source', $facility->coordinate_source ?? 'MANUAL') }}',
        provinceId:   '{{ old('province_id',    $facility->province_id    ?? '') }}',
        cityId:       '{{ old('city_region_id', $facility->city_region_id ?? '') }}',
        districtId:   '{{ old('district_id',    $facility->district_id    ?? '') }}',
        villageId:    '{{ old('region_id',      $facility->region_id      ?? '') }}',
        cities:       @json($cities   ?? []),
        districts:    @json($districts ?? []),
        villages:     @json($villages  ?? []),
        geocodeStatus: '',
        coordWarning:  false,
        regionWarning: false,
        nudgeStep:     0.001,
        provinceBboxes: @json(config('aksesibel.province_bboxes', [])),
        map:          null,
        marker:       null,

        get sourceLabel() {
            return {
                MANUAL:            'Input manual',
                PIN_PETA:          'Penanda pada peta',
                GEOKODING:         'Hasil geokoding alamat',
                LOKASI_PERANGKAT:  'Lokasi perangkat (GPS)',
            }[this.coordSource] || 'Input manual';
        },

        get accuracyText() {
            return {
                MANUAL:            'Akurasi bergantung pada sumber angka yang dimasukkan.',
                PIN_PETA:          'Perkiraan kasaran berdasarkan posisi penanda di peta.',
                GEOKODING:         'Akurasi bervariasi; sebaiknya verifikasi dengan citra satelit.',
                LOKASI_PERANGKAT:  'Akurasi mengikuti sensor GPS perangkat saat diambil.',
            }[this.coordSource] || '';
        },

        init() {
            this.$nextTick(() => this.initMap());
        },

        initMap() {
            const defaultLat = this.lat  ? parseFloat(this.lat)  : -6.2;
            const defaultLng = this.lng  ? parseFloat(this.lng)  : 106.816;
            const zoom       = this.lat  ? 16 : 12;

            this.map = L.map('facility-map', {
                zoomControl: true,
                keyboard:    true,
            }).setView([defaultLat, defaultLng], zoom);

            L.tileLayer(@json(config('services.osm.tile_url')), {
                attribution: @json(config('services.osm.tile_attribution')),
                maxZoom: {{ (int) config('services.osm.tile_max_zoom', 19) }},
            }).addTo(this.map);

            if (this.lat && this.lng) {
                this.placeMarker(parseFloat(this.lat), parseFloat(this.lng));
            }

            this.map.on('click', (e) => {
                this.lat         = e.latlng.lat.toFixed(7);
                this.lng         = e.latlng.lng.toFixed(7);
                this.coordSource = 'PIN_PETA';
                this.placeMarker(e.latlng.lat, e.latlng.lng);
                this.checkIndonesia();
            });
        },

        placeMarker(lat, lng) {
            if (this.marker) this.map.removeLayer(this.marker);
            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
            this.marker.on('dragend', (e) => {
                const pos     = e.target.getLatLng();
                this.lat      = pos.lat.toFixed(7);
                this.lng      = pos.lng.toFixed(7);
                this.coordSource = 'PIN_PETA';
                this.checkIndonesia();
            });
        },

        updateMarker() {
            if (!this.lat || !this.lng || !this.map) return;
            const lat = parseFloat(this.lat);
            const lng = parseFloat(this.lng);
            if (isNaN(lat) || isNaN(lng)) return;
            this.placeMarker(lat, lng);
            this.map.setView([lat, lng], this.map.getZoom() < 14 ? 15 : this.map.getZoom());
            this.checkIndonesia();
        },

        checkIndonesia() {
            const lat = parseFloat(this.lat);
            const lng = parseFloat(this.lng);
            this.coordWarning = (lat < -11 || lat > 6 || lng < 95 || lng > 141);
            this.checkRegionProximity();
        },

        /** Kode provinsi dari opsi terpilih (data-code). */
        provinceCode() {
            const sel = document.getElementById('province_id');
            const opt = sel?.selectedOptions?.[0];
            return opt?.dataset?.code || null;
        },

        /** Peringatan bila titik jauh dari bounding box provinsi yang dipilih. */
        checkRegionProximity() {
            this.regionWarning = false;
            const code = this.provinceCode();
            const bbox = code ? this.provinceBboxes[code] : null;
            if (!bbox) return;

            const lat = parseFloat(this.lat);
            const lng = parseFloat(this.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            this.regionWarning = (lat < bbox.minLat || lat > bbox.maxLat || lng < bbox.minLng || lng > bbox.maxLng);
        },

        /** Geser titik tanpa mouse sebesar nudgeStep. */
        nudge(dir) {
            if (!this.lat || !this.lng) return;
            let lat = parseFloat(this.lat);
            let lng = parseFloat(this.lng);
            const s = this.nudgeStep;

            if (dir === 'north') lat += s;
            if (dir === 'south') lat -= s;
            if (dir === 'east')  lng += s;
            if (dir === 'west')  lng -= s;

            lat = Math.max(-90,  Math.min(90,  lat));
            lng = Math.max(-180, Math.min(180, lng));

            this.lat = lat.toFixed(7);
            this.lng = lng.toFixed(7);
            this.coordSource = this.coordSource || 'MANUAL';
            this.updateMarker();
        },

        clearCoords() {
            this.lat = '';
            this.lng = '';
            this.coordWarning = false;
            this.regionWarning = false;
            if (this.marker) { this.map.removeLayer(this.marker); this.marker = null; }
        },

        async geocodeAddress() {
            const address = document.getElementById('address')?.value?.trim();
            if (!address) { this.geocodeStatus = 'Isi alamat jalan terlebih dahulu.'; return; }

            this.geocodeStatus = 'Mencari koordinat…';
            try {
                const resp = await fetch('/api/geocode', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}' },
                    body: JSON.stringify({ q: address }),
                });
                const data = await resp.json();
                if (!resp.ok || data.error) { this.geocodeStatus = data.error || 'Gagal mencari koordinat.'; return; }
                if (!data.results?.length)   { this.geocodeStatus = 'Alamat tidak ditemukan. Coba lebih spesifik.'; return; }

                const r = data.results[0];
                this.lat         = r.lat.toFixed(7);
                this.lng         = r.lng.toFixed(7);
                this.coordSource = 'GEOKODING';
                this.placeMarker(r.lat, r.lng);
                this.map.setView([r.lat, r.lng], 16);
                this.geocodeStatus = 'Koordinat ditemukan: ' + r.display_name.substring(0, 60) + '…';
                this.checkIndonesia();
            } catch (e) {
                this.geocodeStatus = 'Gagal menghubungi layanan geokoding.';
            }
        },

        useMyLocation() {
            if (!navigator.geolocation) { this.geocodeStatus = 'Perangkat tidak mendukung geolokasi.'; return; }
            this.geocodeStatus = 'Meminta izin lokasi…';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.lat         = pos.coords.latitude.toFixed(7);
                    this.lng         = pos.coords.longitude.toFixed(7);
                    this.coordSource = 'LOKASI_PERANGKAT';
                    this.placeMarker(pos.coords.latitude, pos.coords.longitude);
                    this.map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                    this.geocodeStatus = 'Lokasi perangkat digunakan. Pastikan sudah sesuai dengan lokasi fasilitas.';
                    this.checkIndonesia();
                },
                (err) => { this.geocodeStatus = 'Izin lokasi ditolak atau gagal: ' + err.message; }
            );
        },

        async onProvinceChange() {
            this.cityId = ''; this.districtId = ''; this.villageId = '';
            this.districts = []; this.villages = [];
            this.checkRegionProximity();
            if (!this.provinceId) { this.cities = []; return; }
            const resp = await fetch(`/api/wilayah?level=KOTA_KABUPATEN&parent_id=${this.provinceId}`);
            this.cities = await resp.json();
        },

        async onCityChange() {
            this.districtId = ''; this.villageId = '';
            this.villages = [];
            if (!this.cityId) { this.districts = []; return; }
            const resp = await fetch(`/api/wilayah?level=KECAMATAN&parent_id=${this.cityId}`);
            this.districts = await resp.json();
        },

        async onDistrictChange() {
            this.villageId = '';
            if (!this.districtId) { this.villages = []; return; }
            const resp = await fetch(`/api/wilayah?level=KELURAHAN&parent_id=${this.districtId}`);
            this.villages = await resp.json();
        },
    };
}
</script>

</x-layout>
