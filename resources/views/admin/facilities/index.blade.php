<x-layout title="Panel Admin – Fasilitas">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-[#0F172A]">Kelola Fasilitas</h1>
                <p class="text-[#475569] text-sm mt-1">Tambah, ubah, dan hapus data fasilitas publik.</p>
            </div>
            <a href="{{ route('admin.facilities.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-semibold text-sm transition-colors min-h-[44px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Fasilitas
            </a>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('admin.facilities.index') }}" role="search" aria-label="Cari fasilitas" class="mb-6 flex gap-3 flex-wrap">
            <div class="flex-1 min-w-[200px]">
                <label for="q" class="sr-only">Cari nama atau kota</label>
                <input id="q" type="search" name="q" value="{{ request('q') }}"
                       placeholder="Cari nama atau kota…"
                       class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
            </div>
            <div>
                <label for="admin-status" class="sr-only">Filter status</label>
                <select id="admin-status" name="accessibility_status"
                        class="px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Facility::statusOptions() as $value => $label)
                        <option value="{{ $value }}" {{ request('accessibility_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#475569] cursor-pointer hover:bg-[#F8FAFC] min-h-[44px]">
                <input type="checkbox" name="no_coords" value="1" {{ request('no_coords') ? 'checked' : '' }} class="w-4 h-4 text-[#1D4ED8]">
                Belum ada koordinat
            </label>
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-sm font-semibold transition-colors min-h-[44px]">Cari</button>
            @if(request()->hasAny(['q','accessibility_status','no_coords']))
                <a href="{{ route('admin.facilities.index') }}" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#475569] text-sm hover:bg-[#F8FAFC] transition-colors min-h-[44px] flex items-center">Reset</a>
            @endif
        </form>

        {{-- Tabel --}}
        @if($facilities->isEmpty())
            <div class="text-center py-20 bg-white rounded-xl border border-[#E2E8F0]" role="status" aria-live="polite">
                <svg class="w-14 h-14 mx-auto text-[#CBD5E1] mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 9h6M9 13h4"/>
                </svg>
                <p class="text-lg font-semibold text-[#0F172A]">Tidak ada fasilitas</p>
                <p class="text-[#475569] text-sm mt-1 mb-4">Belum ada data yang sesuai dengan filter.</p>
                <a href="{{ route('admin.facilities.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-[#1D4ED8] hover:bg-[#1E40AF] px-4 py-2 rounded-lg transition-colors min-h-[44px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Tambah Fasilitas
                </a>
            </div>
        @else
            <div class="bg-white rounded-xl border border-[#E2E8F0] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" aria-label="Tabel daftar fasilitas">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide">Nama</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide hidden md:table-cell">Kategori</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide hidden sm:table-cell">Wilayah</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide">Status</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide hidden lg:table-cell">Koordinat</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-[#475569] text-xs uppercase tracking-wide hidden xl:table-cell">Diperbarui</th>
                                <th scope="col" class="px-4 py-3 text-right font-semibold text-[#475569] text-xs uppercase tracking-wide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F5F9]">
                            @foreach($facilities as $facility)
                                <tr class="hover:bg-[#F8FAFC] transition-colors">
                                    <td class="px-4 py-3 font-medium text-[#0F172A]">
                                        <span class="line-clamp-1">{{ $facility->name }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-[#475569] hidden md:table-cell">
                                        {{ $facility->category->category_name ?? '–' }}
                                    </td>
                                    <td class="px-4 py-3 text-[#475569] hidden sm:table-cell">
                                        @php
                                            $wilayah = array_filter([
                                                $facility->kelurahan?->name,
                                                $facility->kotaWilayah?->name ?? $facility->city,
                                            ]);
                                        @endphp
                                        @if($wilayah)
                                            <span class="block">{{ $wilayah[0] ?? '' }}</span>
                                            @if(!empty($wilayah[1]))
                                                <span class="block text-xs text-[#94A3B8]">{{ $wilayah[1] }}</span>
                                            @endif
                                        @else
                                            <span class="text-[#94A3B8]">–</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-status-badge :status="$facility->accessibility_status" />
                                    </td>
                                    <td class="px-4 py-3 text-xs hidden lg:table-cell">
                                        @if($facility->hasCoordinates())
                                            <span class="inline-flex items-center gap-1 text-[#15803D]">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                                Ada
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[#94A3B8]">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                                Belum
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-[#94A3B8] text-xs hidden xl:table-cell">
                                        {{ $facility->updated_at?->diffForHumans() ?? '–' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- Lihat --}}
                                            <a href="{{ route('facilities.show', $facility->facility_id) }}"
                                               class="p-2 rounded-lg text-[#475569] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
                                               aria-label="Lihat detail {{ $facility->name }}" title="Lihat">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            {{-- Ubah --}}
                                            <a href="{{ route('admin.facilities.edit', $facility->facility_id) }}"
                                               class="p-2 rounded-lg text-[#475569] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
                                               aria-label="Ubah {{ $facility->name }}" title="Ubah">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                            {{-- Hapus --}}
                                            <button
                                                x-data
                                                @click="$dispatch('open-delete-modal', { id: {{ $facility->facility_id }}, name: '{{ addslashes($facility->name) }}' })"
                                                class="p-2 rounded-lg text-[#475569] hover:bg-[#FEE2E2] hover:text-[#B91C1C] transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
                                                aria-label="Hapus {{ $facility->name }}" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($facilities->hasPages())
                    <div class="px-4 py-3 border-t border-[#E2E8F0]" aria-label="Navigasi halaman">
                        {{ $facilities->links() }}
                    </div>
                @endif
            </div>

            <p class="text-xs text-[#94A3B8] mt-3">
                Menampilkan {{ $facilities->firstItem() }}–{{ $facilities->lastItem() }} dari {{ $facilities->total() }} fasilitas
            </p>
        @endif
    </div>

    {{-- Modal hapus --}}
    <div x-data="{
            show: false,
            facilityId: null,
            facilityName: '',
        }"
         @open-delete-modal.window="show = true; facilityId = $event.detail.id; facilityName = $event.detail.name"
         x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="show = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;"
         role="dialog"
         aria-modal="true"
         aria-labelledby="modal-title">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="show = false" aria-hidden="true"></div>

        {{-- Panel --}}
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-[#FEE2E2] flex items-center justify-center shrink-0" aria-hidden="true">
                    <svg class="w-5 h-5 text-[#B91C1C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </div>
                <div class="flex-1">
                    <h2 id="modal-title" class="text-base font-bold text-[#0F172A]">Hapus fasilitas ini?</h2>
                    <p class="text-sm text-[#475569] mt-1">
                        <strong x-text="facilityName"></strong> akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button @click="show = false"
                        class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#475569] text-sm font-medium hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                    Batal
                </button>
                <form :action="'{{ url('/admin/fasilitas') }}/' + facilityId" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-[#B91C1C] hover:bg-red-800 text-white text-sm font-semibold transition-colors min-h-[44px]">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

</x-layout>
