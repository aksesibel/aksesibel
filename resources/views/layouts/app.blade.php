
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Aksesibel')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>

<body>
    <div class="app">

        <aside class="sidebar">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fa-solid fa-wheelchair-move"></i>
                </div>
                <span class="brand-name">Aksesibel</span>
            </div>

            <div class="sidebar-label">RUANG KERJA</div>

            <nav class="side-menu">
                <a href="{{ route('verifikasi.antrean') }}"
                   class="{{ request()->routeIs('verifikasi.antrean') ? 'active' : '' }}">
                    <i class="fa-solid fa-border-all"></i>
                    Antrean verifikasi
                </a>

                <a href="#">
                    <i class="fa-regular fa-clipboard"></i>
                    Riwayat keputusan
                </a>

                <a href="#">
                    <i class="fa-regular fa-folder-open"></i>
                    Cari fasilitas
                </a>
            </nav>

            <div class="permission-card">
                <strong>Akses sesuai kewenangan</strong>
                <p>Anda masuk sebagai verifikator.</p>
            </div>

            <div class="sidebar-footer">
                Pusat bantuan • Keluar
            </div>
        </aside>

        <main class="main">

            <header class="topbar">
                <div class="breadcrumb">
                    @yield('breadcrumb', 'Aksesibel')
                </div>

                <div class="topbar-right">
                    <button class="illustration-btn">Data ilustrasi</button>
                    <i class="fa-regular fa-bell notification"></i>
                    <button class="admin-btn">
                        <i class="fa-regular fa-user"></i> Admin
                    </button>
                    <span class="admin-name">Siti Rahma</span>
                </div>
            </header>

            <section class="content">
                @yield('content')
            </section>

        </main>
    </div>

    @stack('scripts')
</body>
</html>
