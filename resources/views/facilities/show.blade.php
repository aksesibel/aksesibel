<x-layout :title="$facility->name">

@if($facility->hasCoordinates())
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
@endif

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">

    <nav aria-label="Lokasi halaman" class="mb-6">
        <ol class="flex items-center gap-2 text-sm text-[#475569]">
            <li><a href="{{ route('home') }}" class="hover:text-[#1D4ED8] transition-colors">Beranda</a></li>
            <li aria-hidden="true"><span class="mx-1 text-[#CBD5E1]">/</span></li>
            <li><a href="{{ route('facilities.index') }}" class="hover:text-[#1D4ED8] transition-colors">Fasilitas</a></li>
            <li aria-hidden="true"><span class="mx-1 text-[#CBD5E1]">/</span></li>
            <li aria-current="page" class="text-[#0F172A] font-medium truncate max-w-[200px]">{{ $facility->name }}</li>
        </ol>
    </nav>

    <article>
        <div class="h-48 bg-[#EFF6FF] rounded-xl flex items-center justify-center mb-6" aria-hidden="true">
            <svg class="w-14 h-14 text-[#1D4ED8]/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
        </div>

        <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-[#475569] uppercase tracking-wide bg-[#F1F5F9] px-2.5 py-1 rounded-full">
                    {{ $facility->category?->category_name ?? '–' }}
                </span>
                <x-status-badge :status="$facility->accessibility_status" />
            </div>
            <a href="{{ route('admin.facilities.edit', $facility->facility_id) }}"
               class="inline-flex items-center gap-1.5 text-xs font-medium text-[#475569] border border-[#E2E8F0] px-3 py-1.5 rounded-lg hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Ubah
            </a>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F172A] mb-6">{{ $facility->name }}</h1>

        {{-- Deskripsi --}}
        <section aria-labelledby="desc-h" class="mb-6">
            <h2 id="desc-h" class="text-sm font-semibold text-[#475569] uppercase tracking-wide mb-2">Deskripsi</h2>
            <p class="text-[#0F172A] text-sm leading-relaxed whitespace-pre-line">{{ $facility->description }}</p>
        </section>

        {{-- Lokasi --}}
        <section aria-labelledby="loc-h" class="mb-6">
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
                        @click="navigator.clipboard.writeText('{{ addslashes($facility->full_address) }}').then(() => $dispatch('toast', 'Alamat disalin!'))"
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
                    <a href="{{ route('admin.facilities.edit', $facility->facility_id) }}" class="text-[#1D4ED8] hover:underline ml-auto text-xs">Tambah koordinat</a>
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
        <section aria-labelledby="hours-h" class="mb-6">
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
        <section aria-labelledby="contact-h" class="mb-6">
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

        <footer class="text-xs text-[#94A3B8] border-t border-[#E2E8F0] pt-4 mt-6">
            Diperbarui: {{ $facility->updated_at?->translatedFormat('d F Y') ?? '–' }}
        </footer>
    </article>
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

    L.marker([{{ $facility->latitude }}, {{ $facility->longitude }}])
        .addTo(map)
        .bindPopup('<strong>{{ addslashes($facility->name) }}</strong><br>{{ addslashes($facility->address) }}')
        .openPopup();
});
</script>
@endif

</x-layout>
