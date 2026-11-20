@props(['facility', 'distanceKm' => null])

@php
    // Tampilkan wilayah berjenjang bila tersedia, jika tidak pakai kolom city lama.
    $wilayah = array_filter([
        $facility->kelurahan?->name,
        $facility->kotaWilayah?->name,
    ]);
    $lokasiRingkas = implode(', ', $wilayah) ?: ($facility->city ?? '');
@endphp

<article class="bg-white rounded-xl border border-[#E2E8F0] overflow-hidden flex flex-col hover:shadow-md transition-shadow">
    {{-- Gambar placeholder --}}
    <div class="h-40 bg-[#EFF6FF] flex items-center justify-center shrink-0" aria-hidden="true">
        <svg class="w-12 h-12 text-[#1D4ED8]/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>
        </svg>
    </div>

    <div class="p-4 flex flex-col flex-1 gap-3">
        {{-- Kategori & Status --}}
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <span class="text-xs font-medium text-[#475569] uppercase tracking-wide">
                {{ $facility->category->category_name ?? '–' }}
            </span>
            <x-status-badge :status="$facility->accessibility_status" />
        </div>

        {{-- Nama --}}
        <h3 class="font-bold text-[#0F172A] text-base leading-snug">
            {{ $facility->name }}
        </h3>

        {{-- Alamat + wilayah --}}
        <p class="text-sm text-[#475569] flex items-start gap-1.5">
            <svg class="w-4 h-4 shrink-0 mt-0.5 text-[#1D4ED8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/>
            </svg>
            <span>{{ $facility->address }}@if($lokasiRingkas), {{ $lokasiRingkas }}@endif</span>
        </p>

        {{-- Penanda koordinat & jarak --}}
        <div class="flex items-center gap-3 flex-wrap">
            @if($facility->hasCoordinates())
                <span class="inline-flex items-center gap-1 text-xs font-medium text-[#15803D]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    Koordinat: Ada
                </span>
            @else
                <span class="inline-flex items-center gap-1 text-xs font-medium text-[#94A3B8]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    Lokasi belum ditentukan
                </span>
            @endif

            @if($distanceKm !== null)
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-[#1D4ED8]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
                    {{ number_format($distanceKm, 1, ',', '.') }} km dari Anda
                </span>
            @endif
        </div>

        {{-- Diperbarui --}}
        <p class="text-xs text-[#94A3B8] mt-auto">
            Diperbarui {{ $facility->updated_at ? $facility->updated_at->diffForHumans() : '–' }}
        </p>

        {{-- Link detail --}}
        <a href="{{ route('facilities.show', $facility->facility_id) }}"
           class="inline-flex items-center gap-1 text-sm font-semibold text-[#1D4ED8] hover:text-[#1E40AF] transition-colors"
           aria-label="Lihat detail {{ $facility->name }}">
            Lihat detail
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
</article>
