@props(['facility', 'distanceKm' => null, 'horizontal' => false])

@php
    $region = implode(', ', array_filter([$facility->kelurahan?->name, $facility->kotaWilayah?->name])) ?: $facility->city;
@endphp

<article @class([
    'min-w-0 gap-4 rounded-xl border border-slate-300 bg-white p-3.5 shadow-xs transition-shadow hover:shadow-md',
    'grid grid-cols-1 sm:grid-cols-[minmax(9rem,13rem)_minmax(0,1fr)]' => $horizontal,
    'flex flex-col' => ! $horizontal,
])>
    <x-facility-photo :facility="$facility" :class="$horizontal ? 'min-h-40' : 'aspect-[2.35] w-full'" />
    <div class="flex min-w-0 flex-1 flex-col gap-2">
        <div class="flex flex-wrap items-center justify-between gap-1.5">
            <span class="text-xs text-slate-600">{{ $facility->category?->category_name }}</span>
            <x-status-badge :status="$facility->accessibility_status" />
        </div>
        <h3 class="text-lg leading-tight font-bold">{{ $facility->name }}</h3>
        <p class="flex min-w-0 items-start gap-1.5 text-sm text-slate-600">
            <x-icon name="info" :size="16" class="mt-0.5" />
            <span>{{ $facility->address }}@if($region), {{ $region }}@endif</span>
        </p>
        @if(isset($facility->facility_accessibilities_count) && $facility->facility_accessibilities_count > 0)
            <p class="text-sm font-semibold">{{ $facility->functional_accessibilities_count }} dari {{ $facility->facility_accessibilities_count }} sarana berfungsi</p>
        @else
            <p class="text-xs text-slate-600">Informasi sarana belum tercatat.</p>
        @endif
        @unless($facility->hasCoordinates())
            <p class="text-xs text-slate-600">Lokasi belum ditentukan</p>
        @endunless
        @if($distanceKm !== null)
            <p class="text-xs font-semibold text-primary">{{ number_format($distanceKm, 1, ',', '.') }} km dari Anda</p>
        @endif
        <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-1">
            @if($facility->updated_at)
                <span class="text-xs text-slate-600">Diperbarui {{ $facility->updated_at->translatedFormat('j M Y') }}</span>
            @endif
            <a href="{{ route('facilities.show', $facility) }}" class="ml-auto inline-flex min-h-9 items-center gap-1 text-sm font-semibold text-primary hover:underline" aria-label="Lihat detail {{ $facility->name }}">Lihat detail <x-icon name="arrow" :size="15" /></a>
        </div>
    </div>
</article>
