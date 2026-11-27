<x-layout title="Daftar masyarakat">
    <form class="panel mx-auto max-w-lg space-y-5" method="POST" action="{{ route('register.store') }}">
        @csrf
        <p class="text-sm text-slate-600">Akun baru terdaftar sebagai Masyarakat.</p>
        @include('users.identity-fields', ['record' => null])
        <button class="button" type="submit">Buat akun</button>
    </form>
</x-layout>
