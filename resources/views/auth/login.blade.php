<x-layout title="Masuk">
    <form class="panel mx-auto max-w-lg space-y-5" method="POST" action="{{ route('login.store') }}">
        @csrf
        <div><label for="email">Email</label><input class="field" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus></div>
        <div><label for="password">Password</label><input class="field" id="password" name="password" type="password" autocomplete="current-password" required></div>
        <button class="button" type="submit">Masuk</button>
        <p class="text-sm">Belum punya akun? <a class="text-teal-800 underline" href="{{ route('register') }}">Daftar sebagai masyarakat</a></p>
    </form>
</x-layout>
