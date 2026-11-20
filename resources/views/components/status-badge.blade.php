@props(['status'])

@php
$config = match($status) {
    'AKSES_PENUH'         => ['label' => 'Akses Penuh',         'icon' => 'check-circle',    'class' => 'bg-[#DCFCE7] text-[#15803D]'],
    'AKSES_SEBAGIAN'      => ['label' => 'Akses Sebagian',      'icon' => 'alert-triangle',  'class' => 'bg-[#FEF3C7] text-[#B45309]'],
    'BELUM_AKSESIBEL'     => ['label' => 'Belum Aksesibel',     'icon' => 'x-circle',        'class' => 'bg-[#FEE2E2] text-[#B91C1C]'],
    'MENUNGGU_KONFIRMASI' => ['label' => 'Menunggu Konfirmasi', 'icon' => 'clock',           'class' => 'bg-[#F1F5F9] text-[#475569]'],
    default               => ['label' => $status,               'icon' => 'help-circle',     'class' => 'bg-[#F1F5F9] text-[#475569]'],
};
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $config['class'] }}" aria-label="Status: {{ $config['label'] }}">
    @if($config['icon'] === 'check-circle')
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/><circle cx="12" cy="12" r="10" stroke-opacity="0.3"/></svg>
    @elseif($config['icon'] === 'alert-triangle')
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    @elseif($config['icon'] === 'x-circle')
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
    @else
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v2M12 10v6"/></svg>
    @endif
    {{ $config['label'] }}
</span>
