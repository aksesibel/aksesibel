
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aksesibel') - Aksesibel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #2453e6;
            --navy: #142f63;
            --background: #f5f8fc;
            --text: #172033;
            --muted: #64748b;
            --border: #d5dfeb;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 170px;
            height: 100vh;
            background: var(--navy);
            color: white;
            padding: 18px 14px;
        }

        .brand {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 35px;
        }

        .sidebar-label {
            color: #b9c8e2;
            font-size: 10px;
            margin-bottom: 12px;
        }

        .sidebar a {
            display: block;
            color: #e4ebf8;
            text-decoration: none;
            padding: 11px 10px;
            margin-bottom: 7px;
            border-radius: 7px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: var(--primary);
            color: white;
        }

        .main-content {
            margin-left: 170px;
            min-height: 100vh;
        }

        .topbar {
            height: 58px;
            background: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .page-content {
            padding: 28px 24px;
        }

        .content-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 22px;
            box-shadow: 0 3px 10px rgba(20, 47, 99, .04);
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .form-control,
        .form-select {
            border-color: var(--border);
            padding: 10px 12px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 65px;
                padding: 15px 8px;
            }

            .brand,
            .sidebar-label,
            .sidebar a span {
                display: none;
            }

            .sidebar a {
                text-align: center;
                font-size: 12px;
            }

            .main-content {
                margin-left: 65px;
            }

            .page-content {
                padding: 18px 12px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    <aside class="sidebar">
        <div class="brand">♿ Aksesibel</div>

        <div class="sidebar-label">RUANG KERJA</div>

        <a href="#">
            <span>Laporan saya</span>
        </a>

        <a href="{{ route('issue-categories.index') }}"
           class="{{ request()->routeIs('issue-categories.*') ? 'active' : '' }}">
            <span>Kategori kendala</span>
        </a>

        <a href="#">
            <span>Buat laporan</span>
        </a>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="text-muted">
                Aksesibel / @yield('breadcrumb', 'Dashboard')
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Masyarakat</span>
            </div>
        </header>

        <section class="page-content">
            @yield('content')
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
