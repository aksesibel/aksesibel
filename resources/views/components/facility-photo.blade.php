@props(['facility', 'detail' => false])

@php
    $photo = match ($facility->name) {
        'Perpustakaan Jakarta – Cikini' => $detail ? 'perpustakaan-galeri-utama.jpg' : 'perpustakaan-cikini.jpg',
        'Stasiun MRT Dukuh Atas BNI' => 'mrt-dukuh-atas.jpg',
        'Taman Literasi Martha Tiahahu' => 'taman-literasi.jpg',
        default => null,
    };
@endphp
<div {{ $attributes->merge(['class' => 'relative grid min-w-0 place-items-center overflow-hidden rounded-lg bg-blue-50']) }}>
    @if($photo)
        <img src="{{ asset('images/aksesibel/'.$photo) }}" alt="Foto ilustrasi {{ $facility->name }}" class="absolute inset-0 size-full object-cover" @unless($detail) loading="lazy" @endunless>
    @else
        <x-icon name="building" :size="56" class="text-blue-300" />
        <span class="sr-only">Foto fasilitas belum tersedia</span>
    @endif
</div>
