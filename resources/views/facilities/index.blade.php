<x-layout title="Daftar Fasilitas">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" crossorigin="">

<style>
    /* Kontrol peta minimal 44x44px agar mudah disentuh (WCAG 2.5.5) */
    #list-map .leaflet-control-zoom a,
    #list-map .leaflet-bar a {
        width: 44px; height: 44px; line-height: 44px; font-size: 20px;
    }
    #list-map .leaflet-popup-content { margin: 12px 14px; }
    #list-map .marker-cluster-custom {
        background: rgba(29, 78, 216, 0.18);
        border-radius: 9999px;
        display: flex; align-items: center; justify-content: center;
    }
    #list-map .marker-cluster-custom div {
        width: 40px; height: 40px; border-radius: 9999px;
        background: #1D4ED8; color: #fff; font-weight: 700; font-size: 13px;
        display: flex; align-items: center; justify-content: center;
        border: 3px solid rgba(255,255,255,0.85);
    }
    #list-map .leaflet-marker-icon:focus-visible {
        outline: 3px solid #1D4ED8; outline-offset: 2px; border-radius: 50%;
    }
    @media (prefers-reduced-motion: reduce) {
        #map-sheet { transition: none !important; }
    }
</style>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10" x-data="facilityList()" x-init="init()">

    <h1 class="text-2xl font-extrabold text-[#0F172A] mb-1">Daftar Fasilitas Publik</h1>
    <p class="text-[#475569] text-sm mb-6">Temukan fasilitas yang aksesibel di sekitar Anda.</p>

    {{-- Chip filter kategori --}}
    <div class="flex flex-wrap gap-2 mb-6" role="group" aria-label="Filter berdasarkan kategori">
        <a href="{{ route('facilities.index', array_merge(request()->except('category_id'), ['page' => 1])) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold border transition-colors min-h-[44px]
                  {{ !request('category_id') ? 'bg-[#1D4ED8] text-white border-[#1D4ED8]' : 'bg-white text-[#475569] border-[#E2E8F0] hover:border-[#1D4ED8] hover:text-[#1D4ED8]' }}"
           aria-pressed="{{ !request('category_id') ? 'true' : 'false' }}">Semua</a>
        @foreach($categories as $cat)
            @php $isActive = request('category_id') == $cat->category_id; @endphp
            <a href="{{ route('facilities.index', array_merge(request()->except('category_id'), ['category_id' => $cat->category_id, 'page' => 1])) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold border transition-colors min-h-[44px]
                      {{ $isActive ? 'bg-[#1D4ED8] text-white border-[#1D4ED8]' : 'bg-white text-[#475569] border-[#E2E8F0] hover:border-[#1D4ED8] hover:text-[#1D4ED8]' }}"
               aria-pressed="{{ $isActive ? 'true' : 'false' }}">
                <x-category-icon :icon="$cat->icon" class="w-3.5 h-3.5" />
                {{ $cat->category_name }}
                <span class="{{ $isActive ? 'bg-white/20 text-white' : 'bg-[#F1F5F9] text-[#475569]' }} text-xs px-1.5 py-0.5 rounded-full font-semibold">{{ $cat->facilities_count }}</span>
            </a>
        @endforeach
    </div>

    {{-- Search & filter (tersinkron dengan query string URL) --}}
    <form method="GET" action="{{ route('facilities.index') }}" role="search" aria-label="Filter fasilitas"
          x-ref="filterForm"
          class="bg-white border border-[#E2E8F0] rounded-xl p-4 mb-6 flex flex-col sm:flex-row gap-3 flex-wrap">
        @if(request('category_id'))<input type="hidden" name="category_id" value="{{ request('category_id') }}">@endif
        <input type="hidden" name="sort" x-model="sortBy">
        <input type="hidden" name="lat" x-model="userLat">
        <input type="hidden" name="lng" x-model="userLng">

        <div class="flex-1 min-w-[180px]">
            <label for="q" class="sr-only">Cari nama, wilayah, atau alamat</label>
            <input id="q" type="search" name="q" value="{{ request('q') }}"
                   placeholder="Cari nama, wilayah, atau alamat…"
                   class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
        </div>

        {{-- Filter wilayah berjenjang --}}
        <div>
            <label for="province_id" class="sr-only">Provinsi</label>
            <select id="province_id" name="province_id" x-model="provinceId" @change="onProvinceChange()"
                    class="px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                <option value="">Semua Provinsi</option>
                @foreach($provinces as $prov)
                    <option value="{{ $prov->region_id }}">{{ $prov->name }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="cities.length" x-cloak>
            <label for="city_id" class="sr-only">Kota/Kabupaten</label>
            <select id="city_id" name="city_id" x-model="cityId" @change="onCityChange()"
                    class="px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                <option value="">Semua Kota</option>
                <template x-for="c in cities" :key="c.region_id">
                    <option :value="c.region_id" x-text="c.name"></option>
                </template>
            </select>
        </div>

        <div x-show="districts.length" x-cloak>
            <label for="district_id" class="sr-only">Kecamatan</label>
            <select id="district_id" name="district_id" x-model="districtId"
                    class="px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                <option value="">Semua Kecamatan</option>
                <template x-for="d in districts" :key="d.region_id">
                    <option :value="d.region_id" x-text="d.name"></option>
                </template>
            </select>
        </div>

        <div>
            <label for="accessibility_status" class="sr-only">Status aksesibilitas</label>
            <select id="accessibility_status" name="accessibility_status"
                    class="px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                <option value="">Semua Status</option>
                @foreach(\App\Models\Facility::statusOptions() as $value => $label)
                    <option value="{{ $value }}" {{ request('accessibility_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="px-5 py-2 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-semibold text-sm transition-colors min-h-[44px] flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            Terapkan
        </button>

        {{-- Dekat saya: memakai Geolocation API, koordinat tidak dikirim/disimpan di server --}}
        <button type="button" @click="useMyLocation()"
                class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#1D4ED8] text-sm font-semibold hover:bg-[#EFF6FF] transition-colors min-h-[44px] flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
            <span x-text="geoBusy ? 'Mencari…' : 'Dekat saya'">Dekat saya</span>
        </button>

        @if(request()->hasAny(['q','category_id','accessibility_status','province_id','city_id','district_id','sort']))
            <a href="{{ route('facilities.index') }}" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#475569] text-sm hover:bg-[#F8FAFC] transition-colors min-h-[44px] flex items-center">Hapus Filter</a>
        @endif
    </form>

    {{-- Status geolokasi --}}
    <p x-show="geoStatus" x-cloak role="status" aria-live="polite" class="mb-4 text-sm text-[#475569]" x-text="geoStatus"></p>

    {{-- Tab Daftar / Peta --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <p class="text-sm text-[#475569]" aria-live="polite" id="result-count">
            @if(!$facilities->isEmpty())
                Menampilkan {{ $facilities->firstItem() }}–{{ $facilities->lastItem() }} dari {{ $facilities->total() }} fasilitas
                @if($sortByDistance)<span class="text-[#1D4ED8] font-medium">· diurutkan dari terdekat</span>@endif
            @endif
        </p>

        <div role="tablist" aria-label="Pilih tampilan" class="flex rounded-lg border border-[#E2E8F0] overflow-hidden bg-white">
            <button role="tab" :aria-selected="tab === 'list' ? 'true' : 'false'" @click="tab = 'list'"
                    id="tab-list" aria-controls="panel-list"
                    class="px-4 py-2 text-sm font-medium transition-colors min-h-[44px] flex items-center gap-1.5"
                    :class="tab === 'list' ? 'bg-[#1D4ED8] text-white' : 'text-[#475569] hover:bg-[#F8FAFC]'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Daftar
            </button>
            <button role="tab" :aria-selected="tab === 'map' ? 'true' : 'false'" @click="tab = 'map'; initMap()"
                    id="tab-map" aria-controls="panel-map"
                    class="px-4 py-2 text-sm font-medium transition-colors min-h-[44px] flex items-center gap-1.5 border-l border-[#E2E8F0]"
                    :class="tab === 'map' ? 'bg-[#1D4ED8] text-white' : 'text-[#475569] hover:bg-[#F8FAFC]'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                Peta
            </button>
        </div>
    </div>

    {{-- ==================== Tampilan Daftar ==================== --}}
    <div id="panel-list" role="tabpanel" aria-labelledby="tab-list" x-show="tab === 'list'">
        @if($facilities->isEmpty())
            <div class="text-center py-20" role="status" aria-live="polite">
                <svg class="w-16 h-16 mx-auto text-[#CBD5E1] mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <p class="text-lg font-semibold text-[#0F172A] mb-1">Fasilitas tidak ditemukan</p>
                <p class="text-[#475569] text-sm">Coba ubah kata kunci atau filter yang digunakan.</p>
                <a href="{{ route('facilities.index') }}" class="mt-4 inline-flex text-sm text-[#1D4ED8] hover:underline">Lihat semua fasilitas</a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($facilities as $facility)
                    <x-facility-card :facility="$facility" :distance-km="$sortByDistance ? ($facility->distance_km ?? null) : null" />
                @endforeach
            </div>
            <div class="mt-10" aria-label="Navigasi halaman">{{ $facilities->links() }}</div>
        @endif
    </div>

    {{-- ==================== Tampilan Peta ==================== --}}
    <div id="panel-map" role="tabpanel" aria-labelledby="tab-map" x-show="tab === 'map'" x-cloak>
        <a href="#after-map-panel"
           class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[60] focus:bg-white focus:px-3 focus:py-2 focus:rounded focus:text-sm focus:text-[#1D4ED8] focus:shadow">
            Lewati peta
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-4 lg:gap-6 lg:items-start">

            {{-- Peta --}}
            <div class="relative">
                <div id="list-map"
                     class="w-full rounded-xl border border-[#E2E8F0]"
                     style="height: 60vh; min-height: 320px;"
                     aria-label="Peta fasilitas publik aksesibel. Gunakan tombol Tab untuk berpindah antar penanda dan Enter untuk membuka detail."
                     role="application">
                </div>

                {{-- Kontrol peta: Cari di area ini --}}
                <div class="absolute top-3 left-3 z-[500] flex flex-wrap gap-2">
                    <button type="button" x-show="areaDirty" x-cloak @click="loadMarkers({ announce: true, fit: false })"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#E2E8F0] shadow text-xs font-semibold text-[#1D4ED8] hover:bg-[#EFF6FF] transition-colors min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        Cari di area ini
                    </button>
                    <button type="button" @click="useMyLocation()"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#E2E8F0] shadow text-xs font-semibold text-[#475569] hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
                        Dekat saya
                    </button>
                </div>

                <p class="text-[0.65rem] text-[#94A3B8] mt-1 text-right">
                    © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" class="underline">OpenStreetMap contributors</a>
                </p>
            </div>

            {{-- Daftar hasil: split view (desktop) / bottom sheet (mobile) --}}
            <aside id="map-sheet"
                   class="bg-white border border-[#E2E8F0] rounded-t-2xl lg:rounded-xl lg:static lg:max-h-[60vh] lg:overflow-y-auto
                          max-lg:fixed max-lg:inset-x-0 max-lg:bottom-0 max-lg:z-40 max-lg:max-h-[55vh] max-lg:overflow-y-auto max-lg:shadow-2xl
                          transition-transform duration-200"
                   :class="sheetOpen ? '' : 'max-lg:translate-y-[calc(100%-56px)]'"
                   aria-label="Daftar fasilitas sesuai area dan filter">

                {{-- Handle bottom sheet (mobile) --}}
                <button type="button" @click="sheetOpen = !sheetOpen"
                        class="lg:hidden w-full flex items-center justify-center gap-2 py-3 min-h-[44px] border-b border-[#F1F5F9]"
                        :aria-expanded="sheetOpen ? 'true' : 'false'" aria-controls="map-sheet-body">
                    <span class="w-10 h-1.5 rounded-full bg-[#CBD5E1]" aria-hidden="true"></span>
                    <span class="text-xs font-semibold text-[#475569]" x-text="sheetOpen ? 'Tutup daftar' : 'Tampilkan daftar'">Tutup daftar</span>
                </button>

                <div id="map-sheet-body" class="p-3 space-y-2">
                    <p id="map-status" role="status" aria-live="polite" class="text-sm text-[#475569] px-1 py-1" x-text="mapStatus"></p>

                    <template x-for="f in results" :key="f.properties.id">
                        <div class="rounded-lg border transition-all"
                             :class="highlightedId === f.properties.id ? 'border-[#1D4ED8] ring-2 ring-[#1D4ED8]/30' : 'border-[#E2E8F0]'"
                             @mouseenter="highlightedId = f.properties.id"
                             @mouseleave="highlightedId = null">
                            <button type="button" class="w-full text-left p-3 min-h-[44px]"
                                    @click="focusMarker(f.properties.id)"
                                    :aria-label="'Tampilkan ' + f.properties.name + ' di peta'">
                                <span class="block font-semibold text-sm text-[#0F172A]" x-text="f.properties.name"></span>
                                <span class="block text-xs text-[#475569] mt-0.5" x-text="f.properties.categoryName"></span>
                                <span class="block text-xs text-[#94A3B8] mt-1"
                                      x-text="[f.properties.address, f.properties.district, f.properties.city].filter(Boolean).join(', ')"></span>
                                <span class="inline-flex items-center gap-1.5 mt-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full"
                                          :class="statusBadgeClass(f.properties.status)">
                                        <span aria-hidden="true" x-text="statusSymbol(f.properties.status)"></span>
                                        <span x-text="statusLabel(f.properties.status)"></span>
                                    </span>
                                    <a :href="f.properties.url" class="text-xs font-semibold text-[#1D4ED8] hover:underline">Lihat detail →</a>
                                </span>
                            </button>
                        </div>
                    </template>

                    <p x-show="!loading && results.length === 0" class="text-sm text-[#94A3B8] px-1 py-4 text-center">
                        Tidak ada fasilitas berkoordinat pada area ini.
                    </p>
                </div>
            </aside>
        </div>

        <div id="after-map-panel"></div>
    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" crossorigin=""></script>
<script>
function facilityList() {
    return {
        tab:        @json(request('view') === 'map' ? 'map' : 'list'),
        mapReady:   false,
        map:        null,
        cluster:    null,
        markerById: {},
        results:    [],
        highlightedId: null,
        mapStatus:  '',
        areaDirty:  false,
        loading:    false,
        sheetOpen:  true,
        suppressMove: false,
        abortController: null,
        moveTimer:  null,
        reducedMotion: false,

        // Filter aktif (tersinkron dengan query string di sisi server)
        provinceId: @json((string) request('province_id', '')),
        cityId:     @json((string) request('city_id', '')),
        districtId: @json((string) request('district_id', '')),
        cities:     @json($cities->map(fn ($c) => ['region_id' => $c->region_id, 'name' => $c->name])->values()),
        districts:  @json($districts->map(fn ($d) => ['region_id' => $d->region_id, 'name' => $d->name])->values()),

        // Koordinat pengguna (hanya di klien, tidak dikirim/disimpan di server)
        userLat: @json((string) request('lat', '')),
        userLng: @json((string) request('lng', '')),
        sortBy:  @json((string) request('sort', '')),
        geoBusy:   false,
        geoStatus: '',

        init() {
            this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (this.tab === 'map') {
                this.$nextTick(() => this.initMap());
            }
        },

        // -----------------------------------------------------------
        // Filter wilayah berjenjang
        // -----------------------------------------------------------
        async onProvinceChange() {
            this.cityId = '';
            this.districtId = '';
            this.districts = [];
            this.cities = [];
            if (!this.provinceId) return;
            const resp = await fetch(`/api/wilayah?level=KOTA_KABUPATEN&parent_id=${encodeURIComponent(this.provinceId)}`);
            this.cities = await resp.json();
        },

        async onCityChange() {
            this.districtId = '';
            this.districts = [];
            if (!this.cityId) return;
            const resp = await fetch(`/api/wilayah?level=KECAMATAN&parent_id=${encodeURIComponent(this.cityId)}`);
            this.districts = await resp.json();
        },

        // -----------------------------------------------------------
        // Geolokasi pengguna (hanya atas permintaan pengguna)
        // -----------------------------------------------------------
        useMyLocation() {
            if (!navigator.geolocation) {
                this.geoStatus = 'Perangkat atau peramban Anda tidak mendukung geolokasi.';
                return;
            }

            this.geoBusy   = true;
            this.geoStatus = 'Meminta izin lokasi untuk mengurutkan fasilitas terdekat. Koordinat Anda tidak disimpan di server.';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.userLat = pos.coords.latitude.toFixed(6);
                    this.userLng = pos.coords.longitude.toFixed(6);
                    this.sortBy  = 'jarak';

                    if (this.map) {
                        this.map.setView([pos.coords.latitude, pos.coords.longitude], 14);
                    }

                    this.geoStatus = 'Lokasi ditemukan. Mengurutkan fasilitas terdekat…';
                    // Muat ulang dengan parameter jarak (server-side sorting & jarak di kartu)
                    this.$refs.filterForm.submit();
                },
                (err) => {
                    this.geoBusy = false;
                    const pesan = {
                        1: 'Izin lokasi ditolak. Aktifkan izin lokasi di peramban untuk memakai fitur ini.',
                        2: 'Lokasi tidak tersedia saat ini.',
                        3: 'Permintaan lokasi melebihi batas waktu.',
                    };
                    this.geoStatus = pesan[err.code] || 'Gagal mendapatkan lokasi Anda.';
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        },

        // -----------------------------------------------------------
        // Peta
        // -----------------------------------------------------------
        initMap() {
            if (this.mapReady) return;
            this.mapReady = true;
            this.mapStatus = 'Memuat peta…';

            this.$nextTick(() => {
                const start = (this.userLat && this.userLng)
                    ? [parseFloat(this.userLat), parseFloat(this.userLng)]
                    : [-6.2, 106.816];

                this.map = L.map('list-map', { keyboard: true, scrollWheelZoom: true }).setView(start, 11);

                L.tileLayer(@json(config('services.osm.tile_url')), {
                    attribution: @json(config('services.osm.tile_attribution')),
                    maxZoom: {{ (int) config('services.osm.tile_max_zoom', 19) }},
                }).addTo(this.map);

                this.cluster = L.markerClusterGroup({
                    showCoverageOnHover: false,
                    maxClusterRadius: 60,
                    iconCreateFunction: (c) => L.divIcon({
                        html: '<div>' + c.getChildCount() + '</div>',
                        className: 'marker-cluster-custom',
                        iconSize: L.point(48, 48),
                    }),
                });
                this.map.addLayer(this.cluster);

                // Perubahan area: tampilkan tombol "Cari di area ini" (tidak memuat otomatis)
                this.map.on('moveend', () => {
                    if (this.suppressMove) return;
                    this.areaDirty = true;
                });

                // Kembalikan fokus ke penanda saat popup ditutup (termasuk via Escape)
                this.map.on('popupclose', () => {
                    const id = this.highlightedId;
                    const marker = id ? this.markerById[id] : null;
                    if (marker && marker.getElement()) marker.getElement().focus();
                });

                this.loadMarkers({ announce: true, fit: true });
            });
        },

        buildParams() {
            const params = new URLSearchParams();
            const b = this.map.getBounds();

            params.set('bbox', [b.getWest(), b.getSouth(), b.getEast(), b.getNorth()].map(v => v.toFixed(6)).join(','));

            const q = document.getElementById('q')?.value?.trim();
            if (q) params.set('q', q);

            const cat = @json((string) request('category_id', ''));
            if (cat) params.set('category_id', cat);

            const status = document.getElementById('accessibility_status')?.value;
            if (status) params.set('accessibility_status', status);

            if (this.provinceId) params.set('province_id', this.provinceId);
            if (this.cityId)     params.set('city_id', this.cityId);
            if (this.districtId) params.set('district_id', this.districtId);

            return params;
        },

        async loadMarkers({ announce = true, fit = false } = {}) {
            if (!this.map) return;

            // Batalkan permintaan sebelumnya
            if (this.abortController) this.abortController.abort();
            this.abortController = new AbortController();

            this.loading = true;
            if (announce) this.mapStatus = 'Memuat penanda…';

            try {
                const resp = await fetch(`/api/fasilitas/peta?${this.buildParams().toString()}`, {
                    signal: this.abortController.signal,
                    headers: { 'Accept': 'application/json' },
                });
                const data = await resp.json();

                if (!resp.ok) {
                    this.mapStatus = data.error || 'Gagal memuat penanda.';
                    this.loading = false;
                    return;
                }

                this.results = data.features;
                this.renderMarkers(data.features, fit);
                this.areaDirty = false;

                const meta  = data.meta;
                const extra = meta.truncated ? ` (menampilkan ${meta.returned} dari ${meta.total} — perbesar area untuk hasil lebih spesifik)` : '';
                this.mapStatus = `${meta.returned} fasilitas ditemukan di area ini${extra}.`;
            } catch (e) {
                if (e.name === 'AbortError') return; // permintaan lama, abaikan
                this.mapStatus = 'Gagal memuat penanda. Periksa koneksi internet lalu coba lagi.';
            } finally {
                this.loading = false;
            }
        },

        renderMarkers(features, fit) {
            this.cluster.clearLayers();
            this.markerById = {};

            const bounds = L.latLngBounds([]);

            features.forEach((f) => {
                const p = f.properties;
                const [lng, lat] = f.geometry.coordinates;

                const icon = L.divIcon({
                    className: '',
                    html: `<div style="${this.markerStyle(p.status)}">${this.statusSymbol(p.status)}</div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 30],
                    popupAnchor: [0, -28],
                });

                const marker = L.marker([lat, lng], { icon, keyboard: true, title: p.name });

                marker.bindPopup(`
                    <div style="min-width:200px">
                        <strong style="font-size:13px;color:#0F172A">${this.escapeHtml(p.name)}</strong>
                        <p style="font-size:11px;color:#475569;margin:3px 0 0">${this.escapeHtml(p.categoryName ?? '')}</p>
                        <span style="display:inline-block;margin-top:6px;font-size:11px;font-weight:600;padding:2px 8px;border-radius:9999px;${this.badgeStyle(p.status)}">${this.statusSymbol(p.status)} ${this.statusLabel(p.status)}</span>
                        <p style="font-size:11px;color:#475569;margin:6px 0 0">${this.escapeHtml([p.address, p.district, p.city].filter(Boolean).join(', '))}</p>
                        <a href="${p.url}" style="display:inline-block;margin-top:8px;font-size:12px;color:#1D4ED8;font-weight:600">Lihat detail →</a>
                    </div>
                `);

                marker.on('click', () => { this.highlightedId = p.id; });
                marker.on('popupclose', () => { this.highlightedId = null; });
                marker.on('keypress', (e) => {
                    if (e.originalEvent.key === 'Enter') marker.openPopup();
                });

                this.markerById[p.id] = marker;
                this.cluster.addLayer(marker);
                bounds.extend([lat, lng]);
            });

            if (fit && features.length) {
                this.suppressMove = true;
                this.map.fitBounds(bounds.pad(0.15), { animate: !this.reducedMotion });
                setTimeout(() => { this.suppressMove = false; }, 400);
            }
        },

        focusMarker(id) {
            const marker = this.markerById[id];
            if (!marker || !this.map) return;

            this.highlightedId = id;
            this.suppressMove = true;
            marker.__parent?.zoomToShowLayer ? marker.__parent.zoomToShowLayer(marker, () => marker.openPopup()) : null;

            if (this.reducedMotion) {
                this.map.setView(marker.getLatLng(), Math.max(this.map.getZoom(), 15), { animate: false });
            } else {
                this.map.flyTo(marker.getLatLng(), Math.max(this.map.getZoom(), 15), { duration: 0.5 });
            }
            marker.openPopup();
            setTimeout(() => { this.suppressMove = false; }, 500);
        },

        // -----------------------------------------------------------
        // Gaya penanda: warna + bentuk/ikon berbeda (tidak hanya warna)
        // -----------------------------------------------------------
        markerStyle(status) {
            const map = {
                'AKSES_PENUH':         '#15803D',
                'AKSES_SEBAGIAN':      '#B45309',
                'BELUM_AKSESIBEL':     '#B91C1C',
                'MENUNGGU_KONFIRMASI': '#475569',
            };
            const color = map[status] ?? '#475569';
            return `background:${color};color:#fff;width:30px;height:30px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 2px 4px rgba(0,0,0,.35);font-size:13px;font-weight:700;border-radius:50% 50% 50% 0;transform:rotate(-45deg)`;
        },

        statusSymbol(status) {
            return ({ 'AKSES_PENUH': '✓', 'AKSES_SEBAGIAN': '~', 'BELUM_AKSESIBEL': '✕', 'MENUNGGU_KONFIRMASI': '?' })[status] ?? '•';
        },

        statusLabel(status) {
            return ({ 'AKSES_PENUH': 'Akses Penuh', 'AKSES_SEBAGIAN': 'Akses Sebagian', 'BELUM_AKSESIBEL': 'Belum Aksesibel', 'MENUNGGU_KONFIRMASI': 'Menunggu Konfirmasi' })[status] ?? status;
        },

        statusBadgeClass(status) {
            return ({
                'AKSES_PENUH':         'bg-[#DCFCE7] text-[#15803D]',
                'AKSES_SEBAGIAN':      'bg-[#FEF3C7] text-[#B45309]',
                'BELUM_AKSESIBEL':     'bg-[#FEE2E2] text-[#B91C1C]',
                'MENUNGGU_KONFIRMASI': 'bg-[#F1F5F9] text-[#475569]',
            })[status] ?? 'bg-[#F1F5F9] text-[#475569]';
        },

        badgeStyle(status) {
            return ({
                'AKSES_PENUH':         'background:#DCFCE7;color:#15803D;',
                'AKSES_SEBAGIAN':      'background:#FEF3C7;color:#B45309;',
                'BELUM_AKSESIBEL':     'background:#FEE2E2;color:#B91C1C;',
                'MENUNGGU_KONFIRMASI': 'background:#F1F5F9;color:#475569;',
            })[status] ?? 'background:#F1F5F9;color:#475569;';
        },

        escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (ch) => (
                { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]
            ));
        },
    };
}
</script>

</x-layout>
