@props(['reportUrl', 'accountUrl'])

<footer class="bg-navy text-white">
    <div class="public-container flex flex-col gap-8 py-9 sm:gap-12 sm:py-16">
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white px-5 py-4 text-slate-900 sm:px-6">
            <div>
                <h2 class="font-bold">Laporkan kendala aksesibilitas</h2>
                <p class="mt-1 text-sm text-slate-600">Bantu perbarui informasi fasilitas publik untuk semua pengunjung.</p>
            </div>
            <a class="button" href="{{ $reportUrl }}"><x-icon name="report" :size="17" /> Laporkan kendala</a>
        </div>
        <div class="grid grid-cols-2 gap-7 lg:grid-cols-4">
            <div class="col-span-2 max-w-60 lg:col-span-1">
                <a href="{{ route('home') }}" class="public-brand text-white"><span class="public-brand-mark" aria-hidden="true"><img src="{{ asset('images/aksesibel/logo-publik.svg') }}" alt=""></span>Aksesibel</a>
                <p class="mt-4 text-sm leading-6 text-blue-100">Platform informasi aksesibilitas fasilitas publik untuk Indonesia yang inklusif.</p>
            </div>
            <nav aria-label="Jelajahi Aksesibel" class="footer-links">
                <h2>Jelajahi</h2>
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('facilities.index') }}">Cari fasilitas</a>
                <a href="{{ route('home') }}#panduan">Panduan aksesibilitas</a>
            </nav>
            <nav aria-label="Bantuan laporan" class="footer-links">
                <h2>Bantuan</h2>
                <a href="{{ $reportUrl }}">Laporkan kendala</a>
                <a href="{{ $accountUrl }}">Akun saya</a>
                @if(auth()->user()?->role?->role_name === \App\Models\Role::PUBLIC)
                    <a href="{{ route('reports.index') }}">Laporan saya</a>
                @endif
            </nav>
            <div class="footer-links">
                <h2>Tentang</h2>
                <p>Tim Aksesibel</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-blue-200/40 pt-6 text-xs text-blue-100">
            <span>© {{ now()->year }} Aksesibel</span>
            <a class="font-semibold text-white hover:underline" href="#top">Kembali ke atas</a>
        </div>
    </div>
</footer>
