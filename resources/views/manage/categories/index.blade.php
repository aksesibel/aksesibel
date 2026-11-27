<x-layout :title="$title" subtitle="Kelola kategori bersama untuk seluruh modul. Kategori nonaktif tetap tersimpan dalam histori.">
    <x-slot:actions><a class="button" href="{{ route($routePrefix.'.create') }}"><x-icon name="plus" />Tambah kategori</a></x-slot:actions>

    @isset($categoryCounts)
        <dl aria-label="Ringkasan kategori kendala" class="mb-6 grid gap-4 sm:grid-cols-3">
            @foreach ($categoryCounts as $label => $count)
                <div class="panel">
                    <dt>{{ $label }}</dt>
                    <dd class="text-2xl font-semibold">{{ $count }}</dd>
                </div>
            @endforeach
        </dl>
    @endisset

    <section class="panel">
    <div class="section-heading"><div><h2>Daftar {{ strtolower($title) }}</h2><p>Atur kategori yang tersedia untuk pilihan data baru.</p></div><span class="badge">{{ $categories->total() }} kategori</span></div>
    <form method="GET" action="{{ route($routePrefix.'.index') }}" class="form-grid filter-panel mb-6">
        <div>
            <label for="q">Cari nama kategori</label>
            <input class="field" id="q" name="q" value="{{ request('q') }}" maxlength="255">
        </div>
        <div>
            <label for="status">Status kategori</label>
            <select class="field" id="status" name="status">
                <option value="">Semua status</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="flex flex-wrap items-end gap-2">
            <button class="button" type="submit">Terapkan filter</button>
            <a class="button-secondary" href="{{ route($routePrefix.'.index') }}">Reset filter</a>
        </div>
    </form>

        <div class="table-wrap">
            <table>
                <caption class="sr-only">Daftar {{ $title }}</caption>
                <thead>
                    <tr><th scope="col">Nama kategori</th><th scope="col">Status</th><th scope="col">Digunakan oleh</th><th scope="col">Tindakan</th></tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td><a href="{{ route($routePrefix.'.show', $category) }}">{{ $category->category_name }}</a></td>
                            <td><span class="badge" data-state="{{ $category->is_active ? 'active' : 'inactive' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>{{ $category->{$usageAttribute} }} {{ $usageLabel }}</td>
                            <td><a class="inline-flex items-center gap-2" href="{{ route($routePrefix.'.edit', $category) }}" aria-label="Edit kategori {{ $category->category_name }}">Edit <x-icon name="arrow" :size="16" /></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Tidak ada kategori yang sesuai. Ubah filter atau tambahkan kategori baru.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer"><p>{{ $categories->firstItem() ?? 0 }}–{{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori</p><div>{{ $categories->links() }}</div></div>
    </section>
</x-layout>
