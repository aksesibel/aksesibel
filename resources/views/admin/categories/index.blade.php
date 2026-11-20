<x-layout title="Panel Admin – Kategori">

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-[#0F172A]">Kelola Kategori</h1>
            <p class="text-[#475569] text-sm mt-1">Tambah, ubah urutan, atau nonaktifkan kategori fasilitas.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-semibold text-sm transition-colors min-h-[44px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Kategori
        </a>
    </div>

    {{-- Navigasi tab admin --}}
    <nav aria-label="Tab panel admin" class="flex gap-1 mb-8 border-b border-[#E2E8F0]">
        <a href="{{ route('admin.facilities.index') }}"
           class="px-4 py-2.5 text-sm font-medium text-[#475569] hover:text-[#1D4ED8] transition-colors border-b-2 border-transparent hover:border-[#1D4ED8] -mb-px">
            Fasilitas
        </a>
        <a href="{{ route('admin.categories.index') }}"
           class="px-4 py-2.5 text-sm font-medium text-[#1D4ED8] border-b-2 border-[#1D4ED8] -mb-px"
           aria-current="page">
            Kategori
        </a>
    </nav>

    @if($categories->isEmpty())
        <div class="text-center py-20 bg-white rounded-xl border border-[#E2E8F0]">
            <p class="text-lg font-semibold text-[#0F172A]">Belum ada kategori</p>
            <a href="{{ route('admin.categories.create') }}" class="mt-3 inline-flex text-sm text-[#1D4ED8] hover:underline">Tambah kategori pertama</a>
        </div>
    @else

        {{-- Form reorder --}}
        <form action="{{ route('admin.categories.reorder') }}" method="POST" id="reorder-form">
            @csrf

            <div class="bg-white rounded-xl border border-[#E2E8F0] overflow-hidden"
                 x-data="{
                    items: {{ $categories->pluck('category_id')->toJson() }},
                    announcement: '',
                    moveUp(index) {
                        if (index === 0) return;
                        [this.items[index-1], this.items[index]] = [this.items[index], this.items[index-1]];
                        this.items = [...this.items];
                        const names = Array.from(document.querySelectorAll('[data-category-name]')).map(el => el.dataset.categoryName);
                        this.announcement = names[index-1] + ' dipindahkan ke urutan ' + index + ' dari ' + this.items.length;
                    },
                    moveDown(index) {
                        if (index === this.items.length - 1) return;
                        [this.items[index], this.items[index+1]] = [this.items[index+1], this.items[index]];
                        this.items = [...this.items];
                        const names = Array.from(document.querySelectorAll('[data-category-name]')).map(el => el.dataset.categoryName);
                        this.announcement = names[index+1] + ' dipindahkan ke urutan ' + (index+2) + ' dari ' + this.items.length;
                    }
                 }">

                {{-- Live region untuk aksesibilitas reorder --}}
                <div aria-live="polite" aria-atomic="true" class="sr-only" x-text="announcement"></div>

                {{-- Hidden inputs untuk urutan --}}
                <template x-for="(id, idx) in items" :key="id">
                    <input type="hidden" :name="'order[' + idx + ']'" :value="id">
                </template>

                <table class="w-full text-sm" aria-label="Tabel kategori fasilitas">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide w-10">Urutan</th>
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide">Nama</th>
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide hidden sm:table-cell">Slug</th>
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide hidden md:table-cell">Fasilitas</th>
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide">Status</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold text-[#475569] text-xs uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @foreach($categories as $index => $category)
                        <tr class="hover:bg-[#F8FAFC] transition-colors" data-category-id="{{ $category->category_id }}">
                            {{-- Urutan + tombol naik/turun --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col items-center gap-0.5" x-data>
                                    <button type="button"
                                            @click="moveUp({{ $index }})"
                                            {{ $index === 0 ? 'disabled' : '' }}
                                            class="p-1 rounded text-[#94A3B8] hover:text-[#1D4ED8] hover:bg-[#EFF6FF] disabled:opacity-30 disabled:cursor-not-allowed transition-colors min-h-[28px] min-w-[28px] flex items-center justify-center"
                                            aria-label="Naikkan {{ $category->category_name }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 15l-6-6-6 6"/></svg>
                                    </button>
                                    <span class="text-xs font-mono text-[#94A3B8]">{{ $category->sort_order ?: ($index + 1) }}</span>
                                    <button type="button"
                                            @click="moveDown({{ $index }})"
                                            {{ $index === $categories->count() - 1 ? 'disabled' : '' }}
                                            class="p-1 rounded text-[#94A3B8] hover:text-[#1D4ED8] hover:bg-[#EFF6FF] disabled:opacity-30 disabled:cursor-not-allowed transition-colors min-h-[28px] min-w-[28px] flex items-center justify-center"
                                            aria-label="Turunkan {{ $category->category_name }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                                    </button>
                                </div>
                            </td>

                            {{-- Ikon + Nama --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[#EFF6FF] flex items-center justify-center shrink-0" aria-hidden="true">
                                        <x-category-icon :icon="$category->icon" class="w-4 h-4 text-[#1D4ED8]" />
                                    </div>
                                    <span class="font-medium text-[#0F172A]" data-category-name="{{ $category->category_name }}">
                                        {{ $category->category_name }}
                                    </span>
                                </div>
                            </td>

                            {{-- Slug --}}
                            <td class="px-4 py-3 text-[#475569] font-mono text-xs hidden sm:table-cell">{{ $category->slug }}</td>

                            {{-- Jumlah fasilitas --}}
                            <td class="px-4 py-3 hidden md:table-cell">
                                @if($category->facilities_count > 0)
                                    <a href="{{ route('categories.show', $category->slug) }}"
                                       class="text-[#1D4ED8] hover:underline text-sm font-medium">
                                        {{ $category->facilities_count }} fasilitas
                                    </a>
                                @else
                                    <span class="text-[#94A3B8] text-sm">0 fasilitas</span>
                                @endif
                            </td>

                            {{-- Status badge --}}
                            <td class="px-4 py-3">
                                @if($category->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#DCFCE7] text-[#15803D]">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="3"/></svg>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#F1F5F9] text-[#475569]">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="3"/></svg>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Ubah --}}
                                    <a href="{{ route('admin.categories.edit', $category->category_id) }}"
                                       class="p-2 rounded-lg text-[#475569] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
                                       aria-label="Ubah {{ $category->category_name }}" title="Ubah">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>

                                    {{-- Toggle aktif --}}
                                    <form action="{{ route('admin.categories.toggle-active', $category->category_id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="p-2 rounded-lg transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center {{ $category->is_active ? 'text-[#475569] hover:bg-[#FEF3C7] hover:text-[#B45309]' : 'text-[#475569] hover:bg-[#DCFCE7] hover:text-[#15803D]' }}"
                                                aria-label="{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $category->category_name }}"
                                                title="{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            @if($category->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Hapus --}}
                                    <button
                                        x-data
                                        @click="$dispatch('open-delete-category', {
                                            id: {{ $category->category_id }},
                                            name: '{{ addslashes($category->category_name) }}',
                                            count: {{ $category->facilities_count }}
                                        })"
                                        class="p-2 rounded-lg text-[#475569] hover:bg-[#FEE2E2] hover:text-[#B91C1C] transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
                                        aria-label="Hapus {{ $category->category_name }}" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Tombol simpan urutan --}}
                <div class="px-4 py-3 border-t border-[#E2E8F0] flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#475569] text-sm font-medium hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                        Simpan Urutan
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>

{{-- Modal hapus kategori --}}
<div x-data="{
        show: false,
        categoryId: null,
        categoryName: '',
        facilityCount: 0,
        moveTo: '',
        categories: {{ $categories->where('is_active', true)->map(fn($c) => ['id' => $c->category_id, 'name' => $c->category_name])->values()->toJson() }},
     }"
     @open-delete-category.window="
        show = true;
        categoryId = $event.detail.id;
        categoryName = $event.detail.name;
        facilityCount = $event.detail.count;
        moveTo = '';
     "
     x-show="show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="show = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-cat-title"
     x-trap="show">

    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="show = false" aria-hidden="true"></div>

    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-[#FEE2E2] flex items-center justify-center shrink-0" aria-hidden="true">
                <svg class="w-5 h-5 text-[#B91C1C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h2 id="modal-cat-title" class="text-base font-bold text-[#0F172A]">Hapus Kategori</h2>

                {{-- Kategori kosong --}}
                <template x-if="facilityCount === 0">
                    <p class="text-sm text-[#475569] mt-1">
                        Kategori <strong x-text="categoryName"></strong> akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                    </p>
                </template>

                {{-- Kategori punya fasilitas --}}
                <template x-if="facilityCount > 0">
                    <div class="mt-1">
                        <p class="text-sm text-[#B91C1C] font-medium mb-2">
                            Kategori ini memiliki <span x-text="facilityCount"></span> fasilitas.
                        </p>
                        <p class="text-sm text-[#475569] mb-3">Pilih kategori tujuan untuk memindahkan fasilitas, atau batal.</p>
                        <label for="move-to-select" class="block text-xs font-semibold text-[#0F172A] mb-1">Pindahkan ke</label>
                        <select id="move-to-select"
                                x-model="moveTo"
                                class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                            <option value="">-- Pilih kategori tujuan --</option>
                            <template x-for="cat in categories.filter(c => c.id != categoryId)" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button @click="show = false"
                    class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#475569] text-sm font-medium hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                Batal
            </button>

            {{-- Tombol hapus (disable jika punya fasilitas tapi belum pilih tujuan) --}}
            <form :action="'{{ url('/admin/kategori') }}/' + categoryId" method="POST" class="inline" id="delete-category-form">
                @csrf
                @method('DELETE')
                <input type="hidden" name="move_to" :value="moveTo">
                <button type="submit"
                        :disabled="facilityCount > 0 && !moveTo"
                        class="px-4 py-2 rounded-lg bg-[#B91C1C] hover:bg-red-800 text-white text-sm font-semibold transition-colors min-h-[44px] disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-text="facilityCount > 0 ? 'Pindahkan & Hapus' : 'Ya, Hapus'"></span>
                </button>
            </form>
        </div>
    </div>
</div>

</x-layout>
