<x-layout :title="trim($__env->yieldContent('title', 'Laporan Masyarakat'))" :breadcrumb="trim($__env->yieldContent('breadcrumb', 'Laporan Saya'))" :workspace="true" :show-heading="false" module="reports">
    @if(request()->routeIs('reports.show'))
        <x-slot:head>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                .module-reports .content-card { padding: 1.5rem; border: 1px solid #cbd5e1; border-radius: .75rem; background: white; }
                .module-reports .btn-primary { background-color: #1d4ed8; border-color: #1d4ed8; }
            </style>
        </x-slot:head>
    @endif
    @yield('content')
</x-layout>
