<x-layout title="Beranda">

    {{-- Hero --}}
    <section class="bg-[#EFF6FF] py-16 px-4 sm:px-6" aria-labelledby="hero-heading">
        <div class="max-w-3xl mx-auto text-center">
            <h1 id="hero-heading" class="text-3xl sm:text-4xl font-extrabold text-[#0F172A] leading-tight mb-4">
                Temukan Fasilitas Publik <br class="hidden sm:block"> yang <span class="text-[#1D4ED8]">Aksesibel</span> untuk Semua
            </h1>
            <p class="text-[#475569] text-base sm:text-lg mb-8 max-w-xl mx-auto">
                Informasi aksesibilitas fasilitas publik di Indonesia — akurat, terkini, dan mudah dijangkau.
            </p>
            <form action="{{ route('facilities.index') }}" method="GET" role="search" aria-label="Cari fasilitas">
                <div class="flex flex-col sm:flex-row gap-3 max-w-xl mx-auto">
                    <label for="hero-search" class="sr-only">Nama fasilitas, kota, atau alamat</label>
                    <input id="hero-search" type="search" name="q"
                           placeholder="Nama fasilitas, kota, atau alamat…"
                           class="flex-1 px-4 py-3 rounded-lg border border-[#E2E8F0] bg-white text-[#0F172A] placeholder-[#94A3B8] text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                    <button type="submit"
                            class="px-6 py-3 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-semibold text-sm transition-colors min-h-[44px] flex items-center gap-2 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        Cari
                    </button>
                </div>
            </form>
            <p class="mt-6 text-sm text-[#475569]">
                <span class="text-[#1D4ED8] font-bold text-lg">{{ $totalFacilities }}</span> fasilitas terdaftar
            </p>
        </div>
    </section>

    {{-- Section: Jelajahi Berdasarkan Kategori --}}
    @if($activeCategories->isNotEmpty())
    <section class="max-w-6xl mx-auto px-4 sm:px-6 py-12" aria-labelledby="cat-heading">
        <div class="mb-8">
            <h2 id="cat-heading" class="text-2xl font-extrabold text-[#0F172A]">Jelajahi Berdasarkan Kategori</h2>
            <p class="text-[#475569] mt-1 text-sm">Temukan fasilitas sesuai kebutuhan Anda.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($activeCategories as $cat)
            <a href="{{ route('categories.show', $cat->slug) }}"
               class="group bg-white border border-[#E2E8F0] rounded-xl p-5 flex flex-col gap-3 hover:shadow-md hover:border-[#1D4ED8]/30 transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1D4ED8]">
                <div class="w-11 h-11 rounded-xl bg-[#EFF6FF] flex items-center justify-center shrink-0 group-hover:bg-[#DBEAFE] transition-colors" aria-hidden="true">
                    <x-category-icon :icon="$cat->icon" class="w-6 h-6 text-[#1D4ED8]" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-[#0F172A] text-sm leading-snug">{{ $cat->category_name }}</p>
                    @if($cat->description)
                        <p class="text-xs text-[#475569] mt-1 line-clamp-2">{{ $cat->description }}</p>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-[#1D4ED8] font-semibold">{{ $cat->facilities_count }} fasilitas</span>
                    <span class="text-xs text-[#1D4ED8] font-semibold group-hover:translate-x-0.5 transition-transform" aria-hidden="true">→</span>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Fasilitas terbaru --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 py-12 {{ $activeCategories->isNotEmpty() ? 'pt-0' : '' }}" aria-labelledby="latest-heading">
        <div class="flex items-end justify-between mb-8 gap-4 flex-wrap">
            <div>
                <h2 id="latest-heading" class="text-2xl font-extrabold text-[#0F172A]">Jelajahi Fasilitas di Jakarta</h2>
                <p class="text-[#475569] mt-1 text-sm">Fasilitas publik terbaru yang terdaftar di platform kami.</p>
            </div>
            <a href="{{ route('facilities.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#1D4ED8] hover:text-[#1E40AF] transition-colors whitespace-nowrap">
                Lihat semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
        @if($latestFacilities->isEmpty())
            <p class="text-[#475569] text-center py-12">Belum ada fasilitas terdaftar.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($latestFacilities as $facility)
                    <x-facility-card :facility="$facility" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- CTA: kelola data & akun --}}
    <section class="bg-[#EFF6FF] py-12 px-4 sm:px-6" aria-labelledby="cta-heading">
        <div class="max-w-2xl mx-auto text-center">
            <h2 id="cta-heading" class="text-xl font-bold text-[#0F172A] mb-2">Bersama membangun lingkungan yang inklusif</h2>
            <p class="text-[#475569] text-sm mb-6">Kelola data pendukung dan pantau rekap laporan aksesibilitas fasilitas publik.</p>
            <div class="flex flex-wrap gap-3 justify-center">
                @auth
                    <a href="{{ route('account') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-semibold text-sm transition-colors min-h-[44px]">
                        Buka akun saya
                    </a>
                    @can('manage-supporting-data')
                        <a href="{{ route('manage.statistics') }}"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-lg border border-[#E2E8F0] bg-white text-[#1D4ED8] font-semibold text-sm hover:bg-[#DBEAFE] transition-colors min-h-[44px]">
                            Rekap dan Statistik
                        </a>
                    @endcan
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-semibold text-sm transition-colors min-h-[44px]">
                        Masuk ke akun
                    </a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-lg border border-[#E2E8F0] bg-white text-[#1D4ED8] font-semibold text-sm hover:bg-[#DBEAFE] transition-colors min-h-[44px]">
                        Daftar sebagai masyarakat
                    </a>
                @endauth
                <a href="{{ route('admin.facilities.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-lg border border-[#E2E8F0] bg-white text-[#1D4ED8] font-semibold text-sm hover:bg-[#DBEAFE] transition-colors min-h-[44px]">
                    Buka Panel Admin
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </section>

</x-layout>
