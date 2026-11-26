<x-layout title="Beranda" full-width>
    <section class="bg-hero-bg" aria-labelledby="hero-heading">
        <div class="public-container grid items-center gap-8 py-9 lg:min-h-[610px] lg:grid-cols-[minmax(0,1fr)_minmax(0,0.94fr)] lg:gap-14 lg:py-14">
            <div class="grid gap-5">
                <p class="inline-flex w-fit items-center gap-2 rounded-md border border-blue-400 px-3 py-2 text-xs font-semibold text-primary">
                    <x-icon name="shield" :size="15" /> Ruang publik untuk semua
                </p>
                <h1 id="hero-heading" class="text-4xl leading-[1.15] font-bold tracking-tight sm:text-5xl">Kenali aksesnya.<br class="hidden lg:block"> Rencanakan kunjungan dengan lebih tenang.</h1>
                <p class="max-w-xl text-base leading-7 text-slate-600">Temukan informasi aksesibilitas fasilitas publik, lihat kondisi sarana, dan bantu laporkan kendala di sekitar Anda.</p>
                <form action="{{ route('facilities.index') }}" method="GET" role="search" aria-label="Cari fasilitas" class="rounded-xl border border-slate-300 bg-white p-4">
                    <label for="hero-search">Fasilitas atau lokasi yang ingin Anda kunjungi</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <div class="relative min-w-0 flex-1">
                            <x-icon name="search" :size="17" class="pointer-events-none absolute top-1/2 left-2 -translate-y-1/2 text-primary" />
                            <input id="hero-search" type="search" name="q" placeholder="Nama fasilitas, kota, atau alamat" class="min-h-11 w-full rounded-lg pr-2 pl-9 text-sm" />
                        </div>
                        <button type="submit" class="button"><x-icon name="arrow" :size="16" /> Cari fasilitas</button>
                    </div>
                </form>
                <p class="text-xs text-slate-600">Contoh: perpustakaan, stasiun MRT, taman di Jakarta</p>
            </div>
            <div class="grid gap-3">
                <img src="{{ asset('images/aksesibel/kunjungan-inklusif.jpg') }}" alt="Dua pengunjung melewati jalur landai menuju fasilitas publik" width="1264" height="848" class="aspect-[1.6] w-full rounded-lg object-cover" fetchpriority="high">
                <div class="flex items-start gap-3 rounded-lg bg-white p-4">
                    <x-icon name="shield" :size="21" class="text-primary" />
                    <div><p class="text-sm font-semibold">Informasi yang membantu Anda bersiap</p><p class="mt-1 text-xs text-slate-600">Kondisi per sarana, bukti foto, dan tanggal pembaruan.</p></div>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="Cakupan layanan" class="border-y border-slate-200 bg-white">
        <dl class="public-container grid grid-cols-3 gap-3 py-5 lg:py-7">
            @foreach([['Fasilitas terdata', $totalFacilities], ['Jenis sarana aksesibilitas', $totalAccessibilityTypes], ['Laporan selesai', $completedReports]] as [$label, $count])
                <div class="flex min-w-0 flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">
                    <dd class="text-2xl font-bold text-primary sm:text-3xl">{{ number_format($count, 0, ',', '.') }}</dd>
                    <dt class="text-xs leading-tight text-slate-600 sm:text-sm">{{ $label }}</dt>
                </div>
            @endforeach
        </dl>
    </section>

    <section class="public-container grid gap-6 py-10 lg:py-12" aria-labelledby="latest-heading">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div><h2 id="latest-heading" class="text-xl font-bold">Jelajahi fasilitas di Jakarta</h2><p class="mt-2 text-sm text-slate-600">Periksa kondisi tiap sarana sebelum berangkat.</p></div>
            <a href="{{ route('facilities.index') }}" class="button-secondary"><x-icon name="arrow" :size="16" /> Lihat semua fasilitas</a>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            @forelse($latestFacilities as $facility)
                <x-facility-card :facility="$facility" />
            @empty
                <p class="panel col-span-full text-sm text-slate-600">Belum ada fasilitas terdata.</p>
            @endforelse
        </div>
        @if($activeCategories->isNotEmpty())
            <nav aria-label="Kategori fasilitas" class="flex flex-wrap gap-2">
                @foreach($activeCategories as $category)
                    <a class="module-tab" href="{{ route('categories.show', $category->slug) }}">{{ $category->category_name }}</a>
                @endforeach
            </nav>
        @endif
    </section>

    <section id="panduan" class="public-container grid scroll-mt-24 gap-6 pb-10 lg:pb-12" aria-labelledby="panduan-heading">
        <div>
            <h2 id="panduan-heading" class="text-xl font-bold">Aksesibilitas bukan hanya soal ramp</h2>
            <p class="mt-2 text-sm text-slate-600">Informasi untuk pengguna kursi roda, penyandang disabilitas, lansia, ibu hamil, dan pendamping.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            @foreach([
                ['panduan-rute.svg', 'Rencanakan jalur Anda', 'Periksa jalur landai, lift, dan pintu masuk tanpa anak tangga.'],
                ['panduan-sensorik.svg', 'Kenali dukungan sensorik', 'Cari penanda arah, jalur pemandu, dan sarana bantu dengar.'],
                ['panduan-pendamping.svg', 'Berkunjung bersama pendamping', 'Cek toilet aksesibel, parkir prioritas, dan bantuan di lokasi.'],
            ] as [$icon, $heading, $description])
                <article class="panel grid content-start gap-3">
                    <img src="{{ asset('images/aksesibel/'.$icon) }}" alt="">
                    <h3 class="font-bold">{{ $heading }}</h3>
                    <p class="text-sm leading-6 text-slate-600">{{ $description }}</p>
                </article>
            @endforeach
        </div>
    </section>
</x-layout>
