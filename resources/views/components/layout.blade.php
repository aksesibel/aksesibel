<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $metaDesc ?? 'Platform informasi aksesibilitas fasilitas publik di Indonesia.' }}">
    <title>{{ $title ?? 'Aksesibel' }} – Aksesibel</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-[#F8FAFC] text-[#0F172A] antialiased" x-data>

    {{-- Header --}}
    <header class="bg-white border-b border-[#E2E8F0] sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1D4ED8] rounded-lg" aria-label="Aksesibel – Beranda">
                <div class="w-9 h-9 rounded-lg bg-[#1D4ED8] flex items-center justify-center shrink-0" aria-hidden="true">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="5" r="2"/>
                        <path d="M12 7v5l3 3M6 11a6 6 0 1 0 12 0"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-[#0F172A] tracking-tight">Aksesibel</span>
            </a>

            <nav aria-label="Navigasi utama" class="hidden md:flex items-center gap-6">
                <a href="{{ route('home') }}" class="text-sm font-medium text-[#475569] hover:text-[#1D4ED8] transition-colors {{ request()->routeIs('home') ? 'text-[#1D4ED8]' : '' }}">Beranda</a>
                <a href="{{ route('facilities.index') }}" class="text-sm font-medium text-[#475569] hover:text-[#1D4ED8] transition-colors {{ request()->routeIs('facilities.*') ? 'text-[#1D4ED8]' : '' }}">Cari Fasilitas</a>
                <a href="{{ route('admin.facilities.index') }}" class="text-sm font-medium text-[#475569] hover:text-[#1D4ED8] transition-colors {{ request()->routeIs('admin.*') ? 'text-[#1D4ED8]' : '' }}">Panel Admin</a>
            </nav>

            {{-- Tombol aksi --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.facilities.create') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-sm font-semibold transition-colors min-h-[44px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Tambah Fasilitas
                </a>

                {{-- Mobile menu toggle --}}
                <button class="md:hidden p-2 rounded-lg text-[#475569] hover:bg-[#F1F5F9] transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
                        x-data="{ open: false }"
                        @click="open = !open"
                        :aria-expanded="open"
                        aria-controls="mobile-nav"
                        aria-label="Buka menu navigasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path x-show="!open" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="open" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    {{-- Toast notifikasi --}}
    @if (session('toast_success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             role="status"
             aria-live="polite"
             class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-[#15803D] text-white px-5 py-3 rounded-xl shadow-lg max-w-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            <span class="text-sm font-medium">{{ session('toast_success') }}</span>
            <button @click="show = false" class="ml-auto p-1 rounded hover:bg-white/20 transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Tutup notifikasi">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if (session('toast_error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             role="alert"
             aria-live="assertive"
             class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-[#B91C1C] text-white px-5 py-3 rounded-xl shadow-lg max-w-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span class="text-sm font-medium">{{ session('toast_error') }}</span>
            <button @click="show = false" class="ml-auto p-1 rounded hover:bg-white/20 transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Tutup notifikasi">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    {{-- Konten utama --}}
    <main id="main-content" class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-[#0F2557] text-white mt-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center" aria-hidden="true">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><path d="M12 7v5l3 3M6 11a6 6 0 1 0 12 0"/></svg>
                        </div>
                        <span class="font-bold text-lg">Aksesibel</span>
                    </div>
                    <p class="text-white/70 text-sm max-w-sm">Platform informasi aksesibilitas fasilitas publik di Indonesia untuk semua.</p>
                </div>
                <nav aria-label="Navigasi footer" class="flex gap-6">
                    <a href="{{ route('home') }}" class="text-white/70 hover:text-white text-sm transition-colors">Beranda</a>
                    <a href="{{ route('facilities.index') }}" class="text-white/70 hover:text-white text-sm transition-colors">Fasilitas</a>
                    <a href="{{ route('admin.facilities.index') }}" class="text-white/70 hover:text-white text-sm transition-colors">Admin Fasilitas</a>
                    <a href="{{ route('admin.categories.index') }}" class="text-white/70 hover:text-white text-sm transition-colors">Admin Kategori</a>
                </nav>
            </div>
            <div class="border-t border-white/10 mt-8 pt-6 text-center text-white/50 text-xs">
                &copy; {{ date('Y') }} Aksesibel. Dibuat untuk mendukung aksesibilitas publik di Indonesia.
            </div>
        </div>
    </footer>

</body>
</html>
