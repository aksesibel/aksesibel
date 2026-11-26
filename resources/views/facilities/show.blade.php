<x-layout :title="$facility->name">
    @if($facility->hasCoordinates())
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
        @endpush
    @endif
    @php
        $items = $facility->facilityAccessibilities;
        $lastInspection = $items->max('inspections_max_inspection_date');
        $photoGallery = $facility->name === 'Perpustakaan Jakarta – Cikini';
        $reportAction = auth()->user()?->role?->role_name === \App\Models\Role::PUBLIC ? 'reports.create' : 'login';
    @endphp

    <div class="grid gap-6 pb-6">
        <div>
            <p class="mb-2 text-xs font-semibold text-primary"><a href="{{ route('home') }}" class="hover:underline">Beranda</a> / <a href="{{ route('facilities.index') }}" class="hover:underline">Cari fasilitas</a> / Detail fasilitas</p>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div><h1 class="text-2xl font-bold tracking-tight sm:text-[32px]">{{ $facility->name }}</h1><p class="mt-2 text-sm text-slate-600">{{ $facility->full_address }}</p></div>
                <x-status-badge :status="$facility->accessibility_status" />
            </div>
        </div>

        <div @class(['grid min-w-0 gap-3', 'sm:grid-cols-[minmax(0,3fr)_minmax(0,1fr)]' => $photoGallery])>
            <x-facility-photo :facility="$facility" detail class="min-h-52 sm:min-h-80" />
            @if($photoGallery)
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-1 sm:grid-rows-2">
                    <img src="{{ asset('images/aksesibel/perpustakaan-galeri-sarana.jpg') }}" alt="Ilustrasi jalur landai di fasilitas" class="size-full min-h-24 rounded-lg object-cover" loading="lazy">
                    <img src="{{ asset('images/aksesibel/perpustakaan-galeri-akses.jpg') }}" alt="Ilustrasi jalur pemandu di dalam fasilitas" class="size-full min-h-24 rounded-lg object-cover" loading="lazy">
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
            @if($facility->category)
                <span class="module-tab"><x-icon name="building" :size="15" />{{ $facility->category->category_name }}</span>
            @endif
            @if($lastInspection)
                <span class="module-tab is-active"><x-icon name="shield" :size="15" />Diperiksa {{ \Illuminate\Support\Carbon::parse($lastInspection)->translatedFormat('j M Y') }}</span>
            @endif
            @if($photoGallery)
                <span>Foto ilustrasi fasilitas dan sarana.</span>
            @endif
            @can('manage-public-facilities')
                <a class="button-secondary ml-auto" href="{{ route('admin.facilities.edit', $facility) }}">Ubah fasilitas</a>
            @endcan
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,2.65fr)_minmax(0,1fr)]">
            <div class="grid min-w-0 gap-5">
                <section class="panel" aria-labelledby="sarana-heading">
                    <div class="flex justify-between gap-3">
                        <div>
                            <h2 id="sarana-heading" class="text-lg font-bold">Kondisi sarana aksesibilitas</h2>
                            @if($items->isNotEmpty())<p class="mt-1 text-sm text-slate-600">{{ $items->count() }} sarana terdata</p>@endif
                        </div>
                        <x-icon name="shield" class="text-primary" :size="23" />
                    </div>
                    @if($facility->entrance_note)
                        <p class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-900">{{ $facility->entrance_note }}</p>
                    @endif
                    <ul class="mt-3 divide-y divide-slate-200">
                        @forelse($items as $item)
                            @php
                                $availability = $item->availability_status;
                                $condition = $item->condition_status;
                                $label = match (true) {
                                    $availability === \App\Models\FacilityAccessibility::AVAILABILITY_UNAVAILABLE => 'Belum tersedia',
                                    $condition === \App\Models\FacilityAccessibility::CONDITION_GOOD => 'Berfungsi',
                                    filled($condition) => \App\Models\FacilityAccessibility::CONDITION_LABELS[$condition] ?? 'Belum diperiksa',
                                    default => 'Belum diperiksa',
                                };
                            @endphp
                            <li class="flex items-start justify-between gap-3 py-4">
                                <div class="flex min-w-0 items-start gap-3">
                                    <x-icon name="check" :size="19" class="mt-0.5 text-primary" />
                                    <div>
                                        <p class="font-semibold">{{ $item->accessibilityType?->type_name ?? 'Sarana' }}</p>
                                        @if($item->location_description)<p class="mt-1 text-sm text-slate-600">{{ $item->location_description }}</p>@endif
                                    </div>
                                </div>
                                <span @class(['badge text-center', 'bg-emerald-50 text-emerald-800' => $label === 'Berfungsi', 'bg-amber-50 text-amber-900' => str_starts_with($label, 'Rusak')])>{{ $label }}</span>
                            </li>
                        @empty
                            <li class="py-2 text-sm text-slate-600">Informasi kondisi sarana belum tercatat.</li>
                        @endforelse
                    </ul>
                </section>
                @if($facility->description)
                    <section class="panel"><h2 class="text-lg font-bold">Tentang fasilitas</h2><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $facility->description }}</p></section>
                @endif
                <section class="panel">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-lg font-bold">Laporan masyarakat</h2>
                        <span class="badge">{{ $activeReports }} laporan aktif</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Sampaikan kondisi sarana yang perlu ditinjau di fasilitas ini.</p>
                    <a href="{{ route($reportAction) }}" class="button mt-4"><x-icon name="report" :size="16" />{{ $reportAction === 'reports.create' ? 'Buat laporan' : 'Masuk untuk melapor' }}</a>
                </section>
            </div>
            <aside class="panel min-w-0 !p-4 sm:!p-5" aria-label="Informasi kunjungan">
                <h2 class="mb-4 text-lg font-bold">Rencanakan kunjungan</h2>
        {{-- Lokasi --}}
        <section aria-labelledby="loc-h" class="mb-6 last:mb-0">
            <h2 id="loc-h" class="text-sm font-semibold text-[#475569] uppercase tracking-wide mb-3">Lokasi</h2>

            {{-- Alamat --}}
            <div class="flex items-start gap-2 mb-3">
                <svg class="w-4 h-4 shrink-0 mt-0.5 text-[#1D4ED8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                <div>
                    <p class="text-sm text-[#0F172A]">{{ $facility->address }}</p>
                    @php
                        $wilayah = array_filter([
                            $facility->kelurahan?->name,
                            $facility->kecamatan?->name,
                            $facility->kotaWilayah?->name ?? $facility->city,
                            $facility->provinsi?->name,
                        ]);
                    @endphp
                    @if($wilayah)
                        <p class="text-sm text-[#475569] mt-0.5">{{ implode(', ', $wilayah) }}{{ $facility->postal_code ? ' ' . $facility->postal_code : '' }}</p>
                    @endif
                </div>
            </div>

            {{-- Tombol salin & petunjuk arah --}}
            <div class="flex flex-wrap gap-2 mb-4" x-data>
                <button type="button"
                        @click="navigator.clipboard.writeText((@js($facility->full_address))).then(() => $dispatch('toast', 'Alamat disalin!'))"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-[#E2E8F0] text-xs font-medium text-[#475569] hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    Salin alamat
                </button>
                @if($facility->hasCoordinates())
                    <a href="{{ config('services.osm.directions_url') }}?from=&to={{ $facility->latitude }},{{ $facility->longitude }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-[#E2E8F0] text-xs font-medium text-[#475569] hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                        Petunjuk arah
                    </a>
                    <a href="https://maps.google.com/?q={{ $facility->latitude }},{{ $facility->longitude }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-[#E2E8F0] text-xs font-medium text-[#475569] hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                        Google Maps
                    </a>
                @endif
            </div>

            {{-- Peta mini --}}
            @if($facility->hasCoordinates())
                <div class="relative mb-2">
                    <a href="#after-mini-map" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-10 focus:bg-white focus:px-3 focus:py-1 focus:rounded focus:text-sm focus:text-[#1D4ED8]">Lewati peta</a>
                    <div id="mini-map" class="w-full h-56 rounded-xl border border-[#E2E8F0]"
                         aria-label="Peta lokasi {{ $facility->name }}" role="img"></div>
                    <p class="text-[0.65rem] text-[#94A3B8] mt-1 text-right">© <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" class="underline">OpenStreetMap contributors</a></p>
                </div>
                <div id="after-mini-map"></div>
            @else
                <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-4 flex items-center gap-3 text-sm text-[#475569]">
                    <svg class="w-5 h-5 text-[#CBD5E1] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    Lokasi belum ditentukan.
                    @can('manage-public-facilities')
                        <a href="{{ route('admin.facilities.edit', $facility->facility_id) }}" class="text-[#1D4ED8] hover:underline ml-auto text-xs">Tambah koordinat</a>
                    @endcan
                </div>
            @endif

            {{-- Catatan lokasi --}}
            @if($facility->location_note)
                <div class="mt-3 bg-[#EFF6FF] rounded-lg px-3 py-2 text-sm text-[#1D4ED8]">
                    <span class="font-semibold">Patokan:</span> {{ $facility->location_note }}
                </div>
            @endif

            @if($facility->entrance_note)
                <div class="mt-2 bg-[#DCFCE7] rounded-lg px-3 py-2 text-sm text-[#15803D]">
                    <span class="font-semibold">Pintu masuk aksesibel:</span> {{ $facility->entrance_note }}
                </div>
            @endif

            @if($facility->nearby_transit)
                <p class="mt-2 text-sm text-[#475569] flex items-start gap-1.5">
                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-[#1D4ED8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="16" rx="2"/><path d="M4 11h16M12 3v16"/></svg>
                    <span><span class="font-semibold text-[#0F172A]">Transportasi terdekat:</span> {{ $facility->nearby_transit }}</span>
                </p>
            @endif
        </section>

        {{-- Jam Operasional --}}
        @if($facility->operating_hours)
        @php
            $hours   = json_decode($facility->operating_hours, true) ?? [];
            $hariMap = ['senin'=>'Senin','selasa'=>'Selasa','rabu'=>'Rabu','kamis'=>'Kamis','jumat'=>'Jumat','sabtu'=>'Sabtu','minggu'=>'Minggu'];
            $hariIni = strtolower(now()->locale('id')->dayName);
        @endphp
        <section aria-labelledby="hours-h" class="mb-6 last:mb-0">
            <h2 id="hours-h" class="text-sm font-semibold text-[#475569] uppercase tracking-wide mb-3">Jam Operasional</h2>
            <div class="bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] overflow-hidden">
                <table class="w-full text-sm" aria-label="Jam operasional per hari">
                    <tbody>
                        @foreach($hariMap as $key => $label)
                            @php $d = $hours[$key] ?? null; $today = $hariIni === $key; @endphp
                            <tr class="border-b border-[#E2E8F0] last:border-0 {{ $today ? 'bg-[#EFF6FF]' : '' }}">
                                <td class="px-4 py-2.5 font-medium text-[#0F172A] w-28">
                                    {{ $label }} @if($today)<span class="ml-1 text-[0.65rem] font-bold text-[#1D4ED8] uppercase">Hari ini</span>@endif
                                </td>
                                <td class="px-4 py-2.5 text-[#475569]">
                                    @if(!$d || isset($d['closed'])) <span class="text-[#B91C1C] font-medium">Tutup</span>
                                    @elseif(isset($d['buka_24_jam'])) <span class="text-[#15803D] font-medium">Buka 24 Jam</span>
                                    @else {{ $d['open'] ?? '–' }} – {{ $d['close'] ?? '–' }} @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Kontak --}}
        @if($facility->phone || $facility->email)
        <section aria-labelledby="contact-h" class="mb-6 last:mb-0">
            <h2 id="contact-h" class="text-sm font-semibold text-[#475569] uppercase tracking-wide mb-2">Kontak</h2>
            <div class="flex flex-col gap-2">
                @if($facility->phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $facility->phone) }}" class="inline-flex items-center gap-2 text-sm text-[#1D4ED8] hover:text-[#1E40AF] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 2.09 4.17 2 2 0 0 1 4.09 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        {{ $facility->phone }}
                    </a>
                @endif
                @if($facility->email)
                    <a href="mailto:{{ $facility->email }}" class="inline-flex items-center gap-2 text-sm text-[#1D4ED8] hover:text-[#1E40AF] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        {{ $facility->email }}
                    </a>
                @endif
            </div>
        </section>
        @endif

            </aside>
        </div>
    </div>
@if($facility->hasCoordinates())
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('mini-map', { zoomControl: true, scrollWheelZoom: false })
        .setView([{{ $facility->latitude }}, {{ $facility->longitude }}], 16);

    L.tileLayer(@json(config('services.osm.tile_url')), {
        attribution: @json(config('services.osm.tile_attribution')),
        maxZoom: {{ (int) config('services.osm.tile_max_zoom', 19) }},
    }).addTo(map);

    const popup = document.createElement('div');
    const name = document.createElement('strong');
    name.textContent = @js($facility->name);
    popup.append(name, document.createElement('br'), @js($facility->address ?? ''));

    L.marker([{{ $facility->latitude }}, {{ $facility->longitude }}])
        .addTo(map)
        .bindPopup(popup)
        .openPopup();
});
</script>
@endif

</x-layout>
