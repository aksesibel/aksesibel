
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Aksesibel')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap"
        rel="stylesheet"
    >

    @vite([
    'resources/css/app.css',
    'resources/css/accessibility-types.css',
    'resources/js/app.js'
])

</head>

<body>

{{-- Kumpulan ikon SVG --}}
<svg width="0" height="0"
     style="position:absolute"
     aria-hidden="true"
     focusable="false">

    <symbol id="i-plus" viewBox="0 0 24 24">
        <path d="M5 12h14"/>
        <path d="M12 5v14"/>
    </symbol>

    <symbol id="i-search" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/>
        <path d="m21 21-4.3-4.3"/>
    </symbol>

    <symbol id="i-save" viewBox="0 0 24 24">
        <path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
        <path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/>
        <path d="M7 3v4a1 1 0 0 0 1 1h7"/>
    </symbol>

    <symbol id="i-dashboard" viewBox="0 0 24 24">
        <rect width="7" height="9" x="3" y="3" rx="1"/>
        <rect width="7" height="5" x="14" y="3" rx="1"/>
        <rect width="7" height="9" x="14" y="12" rx="1"/>
        <rect width="7" height="5" x="3" y="16" rx="1"/>
    </symbol>

    <symbol id="i-clipboard" viewBox="0 0 24 24">
        <rect width="8" height="4" x="8" y="2" rx="1"/>
        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
        <path d="M12 11h4"/>
        <path d="M12 16h4"/>
    </symbol>

    <symbol id="i-folder" viewBox="0 0 24 24">
        <path d="m6 14 1.5-2.9A2 2 0 0 1 9.24 10H20a2 2 0 0 1 1.94 2.5l-1.54 6a2 2 0 0 1-1.95 1.5H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3.9a2 2 0 0 1 1.69.9l.81 1.2a2 2 0 0 0 1.67.9H18a2 2 0 0 1 2 2v2"/>
    </symbol>

    <symbol id="i-accessibility" viewBox="0 0 24 24">
        <circle cx="16" cy="4" r="1"/>
        <path d="m18 19 1-7-6 1"/>
        <path d="m5 8 3-3 5.5 3-2.36 3.5"/>
        <path d="M4.24 14.5a5 5 0 0 0 6.88 6"/>
        <path d="M13.76 17.5a5 5 0 0 0-6.88-6"/>
    </symbol>

</svg>

<a class="skip-link" href="#konten">
    Lewati ke konten
</a>

<div class="app">

    {{-- Sidebar --}}
    <aside class="sidebar" aria-label="Navigasi ruang kerja">

        <div class="brand">
            <span class="brand-logo">
                <svg class="icon" width="24" height="24" aria-hidden="true">
                    <use href="#i-accessibility"/>
                </svg>
            </span>

            <span class="brand-name">Aksesibel</span>
        </div>

        <div class="sidebar-section">

            <p class="sidebar-label">RUANG KERJA</p>

            <nav class="sidebar-section" aria-label="Menu">

                <a
                    href="{{ route('accessibility-types.index') }}"
                    class="nav-item {{ request()->routeIs('accessibility-types.*') ? 'active' : '' }}"
                    @if(request()->routeIs('accessibility-types.*'))
                        aria-current="page"
                    @endif
                >
                    <svg class="icon" width="18" height="18" aria-hidden="true">
                        <use href="#i-dashboard"/>
                    </svg>

                    Kelola Jenis Aksesibilitas
                </a>

            </nav>

            <div class="sidebar-note">
                Akses sesuai kewenangan
                <small>Ruang kerja pengelolaan data.</small>
            </div>

            <div class="sidebar-links">
                PBI-04 • Aksesibel
            </div>

        </div>

    </aside>

    {{-- Workspace --}}
    <div class="workspace">

        <header class="topbar">

            <span class="breadcrumb">
                @yield('breadcrumb', 'Aksesibel / Kelola Jenis Aksesibilitas')
            </span>

            <div class="topbar-user">
                <span class="chip active">Admin</span>
                <span class="user-name">Pengelola Data</span>
            </div>

        </header>

        {{-- Konten halaman --}}
        <main id="konten" class="content">

            @if (session('success'))
                <p role="status" class="alert alert-success">
                    {{ session('success') }}
                </p>
            @endif

            @if (session('error'))
                <p role="alert" class="alert alert-danger">
                    {{ session('error') }}
                </p>
            @endif

            @yield('content')

        </main>

    </div>

</div>

</body>
</html>
