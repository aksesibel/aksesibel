<x-layout :title="$title">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <p>Kelola kategori bersama untuk seluruh modul. Kategori nonaktif tetap tersimpan dalam histori.</p>
        <a class="button" href="{{ route($routePrefix.'.create') }}">Tambah kategori</a>
    </div>

    <form method="GET" action="{{ route($routePrefix.'.index') }}" class="panel form-grid">
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

    <div class="panel">
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
                            <td><span class="badge">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>{{ $category->{$usageAttribute} }} {{ $usageLabel }}</td>
                            <td><a href="{{ route($routePrefix.'.edit', $category) }}" aria-label="Edit kategori {{ $category->category_name }}">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Tidak ada kategori yang sesuai. Ubah filter atau tambahkan kategori baru.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $categories->links() }}
    </div>
</x-layout>

