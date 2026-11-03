<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aksesibel')</title>
    <link rel="stylesheet" href="{{ asset('css/accessibility.css') }}">
</head>

<body>
    <main>
        @if (session('success'))
            <p role="status" class="card">{{ session('success') }}</p>
        @endif
        @yield('content')
    </main>
</body>

</html>