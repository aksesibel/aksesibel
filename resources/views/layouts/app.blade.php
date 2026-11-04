<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F5F7FA]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kelola Penugasan Tindak Lanjut') - {{ config('app.name', 'Aksesibel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
    @endif
</head>
<body class="h-full font-sans antialiased text-[#0F172A] bg-[#F5F7FA]">
    <!-- Skip to main content for screen readers -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded-md">
        Lewati ke konten utama
    </a>

    <div class="min-h-full flex flex-col lg:flex-row">
        <!-- SIDEBAR SAMPING (Persis Gambar Figma) -->
        <aside class="w-full lg:w-64 bg-[#0B2546] text-white shrink-0 flex flex-col justify-between border-r border-[#0B2546]/80 lg:min-h-screen">
            <div>
                <!-- Header Logo & Brand -->
                <div class="h-16 flex items-center px-6 border-b border-white/10">
                    <a href="{{ url('/') }}" class="flex items-center space-x-3 text-white font-bold text-xl tracking-tight focus:outline-none focus:ring-2 focus:ring-blue-400 rounded">
                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-[#0B2546] shadow-sm">
                            <svg class="w-5 h-5 text-blue-700" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2a2 2 0 100 4 2 2 0 000-4zm-1 6a1 1 0 00-1 1v5a1 1 0 00.293.707l3 3a1 1 0 001.414-1.414L12.414 14H15a1 1 0 100-2h-3V9a1 1 0 00-1-1zm-4 4a6 6 0 1012 0 1 1 0 10-2 0 4 4 0 11-8 0 1 1 0 10-2 0z"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold">Aksesibel</span>
                    </a>
                </div>

                <!-- Navigasi Ruang Kerja (Persis Gambar 1) -->
                <div class="px-4 py-6 space-y-6">
                    <div>
                        <span class="block px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                            RUANG KERJA
                        </span>
                        <nav class="space-y-1.5" aria-label="Navigasi Utama">
                            <!-- Antrean Penugasan (Ready Reports) -->
                            @if (Auth::user()?->role?->role_name === 'Admin')
                                <a href="{{ route('follow-up-assignments.ready-reports') }}"
                                   class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('follow-up-assignments.ready-reports') ? 'bg-[#1D4ED8] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <span>Antrean Penugasan</span>
                                </a>
                            @endif

                            <!-- Daftar Penugasan -->
                            <a href="{{ route('follow-up-assignments.index') }}"
                               class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('follow-up-assignments.index') ? 'bg-[#1D4ED8] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Daftar Penugasan</span>
                            </a>

                            <!-- Buat Penugasan Baru (Admin) -->
                            @if (Auth::user()?->role?->role_name === 'Admin')
                                <a href="{{ route('follow-up-assignments.create') }}"
                                   class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('follow-up-assignments.create') ? 'bg-[#1D4ED8] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>+ Form Penugasan</span>
                                </a>
                            @endif
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Bagian Bawah Sidebar (Persis Gambar 1) -->
            <div class="p-4 space-y-4">
                <!-- Kartu Akses Sesuai Kewenangan (Persis Gambar 1) -->
                <div class="border border-blue-400/20 bg-blue-950/50 rounded-xl p-3.5 space-y-1.5 shadow-2xs">
                    <div class="flex items-center space-x-2 text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="text-xs font-bold text-white">Akses sesuai kewenangan</span>
                    </div>
                    <p class="text-[11px] text-slate-300 leading-snug">
                        Anda masuk sebagai <span class="font-semibold text-white">{{ Auth::user()?->role?->role_name ?? 'Pengguna' }}</span>.
                    </p>
                </div>

                <!-- Pengalih Akun Cepat (Quick Switcher Pengujian) -->
                <div class="flex items-center justify-between text-[11px] text-slate-300 pt-2 border-t border-white/10">
                    <span>Ganti Akun:</span>
                    <div class="flex items-center space-x-1">
                        <a href="/login-as/admin" title="Login sebagai Admin" class="px-2 py-0.5 rounded text-[10px] font-bold {{ Auth::user()?->role?->role_name === 'Admin' ? 'bg-blue-600 text-white' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}">
                            Admin
                        </a>
                        <a href="/login-as/petugas" title="Login sebagai Petugas" class="px-2 py-0.5 rounded text-[10px] font-bold {{ Auth::user()?->role?->role_name === 'Petugas Tindak Lanjut' ? 'bg-emerald-600 text-white' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}">
                            Petugas
                        </a>
                    </div>
                </div>

                <!-- Footer Sidebar (Persis Gambar 1) -->
                <div class="text-[11px] text-slate-400 flex items-center justify-between pt-1">
                    <span>Pusat bantuan</span>
                    <span>&bull;</span>
                    <a href="/login" class="hover:text-white">Keluar</a>
                </div>
            </div>
        </aside>

        <!-- KONTEN UTAMA (AREA KANAN) -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Top Bar Ringkas (Persis Header Gambar 1) -->
            <header class="bg-white border-b border-[#C3CFDF] h-14 flex items-center justify-between px-6 sticky top-0 z-20">
                <div class="flex items-center space-x-2 text-xs text-[#475569]">
                    <span class="font-bold text-[#0F172A] tracking-tight">Aksesibel</span>
                    <span>/</span>
                    <span class="text-[#475569]">Ruang Kerja Modul Tindak Lanjut</span>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-md border border-[#C3CFDF] text-[11px] font-medium text-[#475569] bg-white">
                        Data ilustrasi
                    </span>
                    <div class="flex items-center space-x-2 border-l border-[#C3CFDF] pl-3 text-xs">
                        <span class="font-bold text-[#0F172A]">{{ Auth::user()?->name ?? 'User' }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ Auth::user()?->role?->role_name === 'Admin' ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ Auth::user()?->role?->role_name ?? 'User' }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main id="main-content" class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6" tabindex="-1">
                <!-- Flash Alert Success -->
                @if (session('success'))
                    <div role="alert" class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-start space-x-3 shadow-2xs">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="text-sm font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                <!-- Flash Alert Error -->
                @if (session('error'))
                    <div role="alert" class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-start space-x-3 shadow-2xs">
                        <svg class="w-5 h-5 text-red-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div class="text-sm font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
