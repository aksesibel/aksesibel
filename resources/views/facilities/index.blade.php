<x-layout title="Daftar Fasilitas">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-2xl font-extrabold text-[#0F172A] mb-1">Daftar Fasilitas Publik</h1>
        <p class="text-[#475569] text-sm mb-6">Temukan fasilitas yang aksesibel di sekitar Anda.</p>

        {{-- Chip filter kategori --}}
        <div class="flex flex-wrap gap-2 mb-6" role="group" aria-label="Filter berdasarkan kategori">
            <a href="{{ route('facilities.index', array_merge(request()->except('category_id'), ['page' => 1])) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold border transition-colors min-h-[44px]
                      {{ !request('category_id') ? 'bg-[#1D4ED8] text-white border-[#1D4ED8]' : 'bg-white text-[#475569] border-[#E2E8F0] hover:border-[#1D4ED8] hover:text-[#1D4ED8]' }}"
               aria-pressed="{{ !request('category_id') ? 'true' : 'false' }}">
                Semua
            </a>
            @foreach($categories as $cat)
                @php $isActive = request('category_id') == $cat->category_id; @endphp
                <a href="{{ route('facilities.index', array_merge(request()->except('category_id'), ['category_id' => $cat->category_id, 'page' => 1])) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold border transition-colors min-h-[44px]
                          {{ $isActive ? 'bg-[#1D4ED8] text-white border-[#1D4ED8]' : 'bg-white text-[#475569] border-[#E2E8F0] hover:border-[#1D4ED8] hover:text-[#1D4ED8]' }}"
                   aria-pressed="{{ $isActive ? 'true' : 'false' }}">
                    <x-category-icon :icon="$cat->icon" class="w-3.5 h-3.5" />
                    {{ $cat->category_name }}
                    <span class="{{ $isActive ? 'bg-white/20' : 'bg-[#F1F5F9]' }} text-xs px-1.5 py-0.5 rounded-full font-semibold {{ $isActive ? 'text-white' : 'text-[#475569]' }}">
                        {{ $cat->facilities_count ?? $cat->facilities()->count() }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Search & filter status --}}
        <form method="GET" action="{{ route('facilities.index') }}" role="search" aria-label="Filter fasilitas"
              class="bg-white border border-[#E2E8F0] rounded-xl p-4 mb-8 flex flex-col sm:flex-row gap-3 flex-wrap">
            {{-- Pertahankan category_id dari chip --}}
            @if(request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif

            <div class="flex-1 min-w-[200px]">
                <label for="q" class="sr-only">Cari nama, kota, atau alamat</label>
                <input id="q" type="search" name="q" value="{{ request('q') }}"
                       placeholder="Cari nama, kota, atau alamat…"
                       class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
            </div>
            <div>
                <label for="accessibility_status" class="sr-only">Filter status aksesibilitas</label>
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
            @if(request()->hasAny(['q','accessibility_status']))
                <a href="{{ route('facilities.index', request()->only('category_id')) }}"
                   class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#475569] text-sm hover:bg-[#F8FAFC] transition-colors min-h-[44px] flex items-center">
                    Hapus Filter
                </a>
            @endif
        </form>

        {{-- Hasil --}}
        @if($facilities->isEmpty())
            <div class="text-center py-20" role="status" aria-live="polite">
                <svg class="w-16 h-16 mx-auto text-[#CBD5E1] mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <p class="text-lg font-semibold text-[#0F172A] mb-1">Fasilitas tidak ditemukan</p>
                <p class="text-[#475569] text-sm">Coba ubah kata kunci atau filter yang digunakan.</p>
                <a href="{{ route('facilities.index') }}" class="mt-4 inline-flex text-sm text-[#1D4ED8] hover:underline">Lihat semua fasilitas</a>
            </div>
        @else
            <p class="text-sm text-[#475569] mb-4" aria-live="polite">
                Menampilkan {{ $facilities->firstItem() }}–{{ $facilities->lastItem() }} dari {{ $facilities->total() }} fasilitas
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($facilities as $facility)
                    <x-facility-card :facility="$facility" />
                @endforeach
            </div>
            <div class="mt-10" aria-label="Navigasi halaman">
                {{ $facilities->links() }}
            </div>
        @endif
    </div>

</x-layout>
