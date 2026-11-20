@props(['name', 'size' => 20])
<svg {{ $attributes->merge(['class' => 'icon']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M15 3.13a4 4 0 0 1 0 7.75"/><circle cx="9" cy="7" r="4"/>
            @break
        @case('grid')
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            @break
        @case('building')
            <path d="M3 21V7h6m0 14V3h9v18M1 21h22M5 11h1m-1 4h1m6-8h3m-3 4h3m-3 4h3m5-4h2v10M12 21v-3h3v3"/>
            @break
        @case('database')
            <ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.66 3.58 3 8 3s8-1.34 8-3V5M4 12c0 1.66 3.58 3 8 3s8-1.34 8-3"/>
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18m-13 5h1m3 0h1m3 0h1"/>
            @break
        @case('chart')
            <path d="M4 3v18h17M8 16v-5m5 5V7m5 9V4"/>
            @break
        @case('report')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z"/><path d="M14 2v6h6M8 13h8m-8 4h6"/>
            @break
        @case('shield')
            <path d="M12 3 3 7v5c0 5 9 10 9 10s9-5 9-10V7l-9-4Z"/><path d="m8 12 3 3 5-6"/>
            @break
        @case('check')
            <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>
            @break
        @case('tool')
            <path d="m14 6 4 4 3-3a6 6 0 0 1-8 8l-6 6a2.12 2.12 0 0 1-3-3l6-6a6 6 0 0 1 8-8l-4 2Z"/>
            @break
        @case('info')
            <circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10h.01"/>
            @break
        @case('search')
            <circle cx="10.5" cy="10.5" r="7.5"/><path d="m16 16 5 5"/>
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('arrow')
            <path d="M4 12h16m-6-6 6 6-6 6"/>
            @break
        @case('logout')
            <path d="M9 5H4v14h5m5-14 7 7-7 7m-6-7h13"/>
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16"/>
            @break
        @case('close')
            <path d="m6 6 12 12M6 18 18 6"/>
            @break
    @endswitch
</svg>
