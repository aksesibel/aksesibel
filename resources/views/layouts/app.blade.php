<x-layout :title="trim($__env->yieldContent('title', 'Verifikasi'))" :breadcrumb="str_replace('Aksesibel / ', '', trim($__env->yieldContent('breadcrumb', 'Verifikasi')))" :workspace="true" :show-heading="false" module="verification">
    @yield('content')
</x-layout>
