@props(['status'])

@php
    $colors = match ($status) {
        'Menunggu Verifikasi' => 'bg-blue-50 text-blue-800',
        'Terverifikasi', 'Selesai' => 'bg-emerald-50 text-emerald-800',
        'Ditolak', 'Dibatalkan' => 'bg-red-50 text-red-800',
        'Dalam Tindak Lanjut', 'Memerlukan Pemeriksaan Lanjutan' => 'bg-amber-50 text-amber-900',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<span {{ $attributes->class(['badge', $colors]) }}>{{ $status ?? '—' }}</span>
