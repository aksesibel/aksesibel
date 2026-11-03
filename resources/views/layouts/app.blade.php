<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kelola Penugasan Tindak Lanjut') - {{ config('app.name', 'Aksesibel') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
    @endif
</head>
<body class="h-full font-sans antialiased text-gray-900 bg-gray-50">
    <!-- Skip to main content for screen readers -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded-md">
        Lewati ke konten utama
    </a>

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-6">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2 text-blue-700 font-bold text-xl tracking-tight focus:outline-none focus:ring-2 focus:ring-blue-500 rounded">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Aksesibel</span>
                    </a>

                    <!-- Navigasi Menu PBI-13 -->
                    <nav class="hidden md:flex space-x-2" aria-label="Navigasi Penugasan">
                        <a href="{{ route('follow-up-assignments.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('follow-up-assignments.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Daftar Penugasan
                        </a>

                        @if (Auth::user()?->role?->role_name === 'Admin')
                            <a href="{{ route('follow-up-assignments.ready-reports') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('follow-up-assignments.ready-reports') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Laporan Siap Ditugaskan
                            </a>
                            <a href="{{ route('follow-up-assignments.create') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('follow-up-assignments.create') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} focus:outline-none focus:ring-2 focus:ring-blue-500">
                                + Form Penugasan
                            </a>
                        @endif
                    </nav>
                </div>

                <!-- User Role Info -->
                <div class="flex items-center space-x-3">
                    @auth
                        <div class="text-right hidden sm:block">
                            <div class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ Auth::user()->role?->role_name === 'Admin' ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ Auth::user()->role?->role_name ?? 'User' }}
                            </span>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" tabindex="-1">
        <!-- Flash Alert Success -->
        @if (session('success'))
            <div role="alert" class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 flex items-start space-x-3 shadow-sm">
                <svg class="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        <!-- Flash Alert Error -->
        @if (session('error'))
            <div role="alert" class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 flex items-start space-x-3 shadow-sm">
                <svg class="w-5 h-5 text-red-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>

