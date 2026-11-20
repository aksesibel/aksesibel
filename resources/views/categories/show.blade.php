<x-layout :title="$category->category_name">

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">

    {{-- Breadcrumb --}}
    <nav aria-label="Lokasi halaman" class="mb-6">
        <ol class="flex items-center gap-2 text-sm text-[#475569]">
            <li><a href="{{ route('home') }}" class="hover:text-[#1D4ED8] transition-colors">Beranda</a></li>
            <li aria-hidden="true"><span class="mx-1 text-[#CBD5E1]">/</span></li>
            <li><a href="{{ route('facilities.index') }}" class="hover:text-[#1D4ED8] transition-colors">Fasilitas</a></li>
            <li aria-hidden="true"><span class="mx-1 text-[#CBD5E1]">/</span></li>
            <li aria-current="page" class="text-[#0F172A] font-medium">{{ $category->category_name }}</li>
        </ol>
    </nav>

    {{-- Header kategori --}}
    <div class="flex items-start gap-4 mb-8">
        <div class="w-14 h-14 rounded-xl bg-[#EFF6FF] flex items-center justify-center shrink-0" aria-hidden="true">
            <x-category-icon :icon="$category->icon" class="w-7 h-7 text-[#1D4ED8]" />
        </div>
        <div>
            <h1 class="text-2xl font-extrabold text-[#0F172A]">{{ $category->category_name }}</h1>
            @if($category->description)
                <p class="text-[#475569] mt-1 text-sm max-w-xl">{{ $category->description }}</p>
            @endif
        </div>
    </div>

    {{-- Filter & Search --}}
    <form method="GET" action="{{ route('categories.show', $category->slug) }}"
          role="search" aria-label="Filter fasilitas dalam kategori"
          class="bg-white border border-[#E2E8F0] rounded-xl p-4 mb-8 flex flex-col sm:flex-row gap-3 flex-wrap">
        <div class="flex-1 min-w-[200px]">
            <label for="q" class="sr-only">Cari nama, kota, atau alamat</label>
            <input id="q" type="search" name="q" value="{{ request('q') }}"
                   placeholder="Cari fasilitas…"
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
        <button type="submit" class="px-5 py-2 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-semibold text-sm transition-colors min-h-[44px]">Cari</button>
        @if(request()->hasAny(['q','accessibility_status']))
            <a href="{{ route('categories.show', $category->slug) }}"
               class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#475569] text-sm hover:bg-[#F8FAFC] transition-colors min-h-[44px] flex items-center">Hapus Filter</a>
        @endif
    </form>

    {{-- Hasil --}}
    @if($facilities->isEmpty())
        <div class="text-center py-20" role="status" aria-live="polite">
            <div class="w-16 h-16 rounded-full bg-[#F1F5F9] flex items-center justify-center mx-auto mb-4" aria-hidden="true">
                <x-category-icon :icon="$category->icon" class="w-8 h-8 text-[#CBD5E1]" />
            </div>
            <p class="text-lg font-semibold text-[#0F172A] mb-1">Belum ada fasilitas</p>
            <p class="text-[#475569] text-sm">Belum ada fasilitas dalam kategori {{ $category->category_name }}.</p>
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
