<x-layout :title="$title.': '.$category->category_name">
    <div class="panel space-y-5">
        <dl class="space-y-3">
            <div><dt class="font-semibold">Nama kategori</dt><dd>{{ $category->category_name }}</dd></div>
            <div><dt class="font-semibold">Deskripsi</dt><dd class="whitespace-pre-line">{{ $category->description ?: 'Belum ada deskripsi.' }}</dd></div>
            <div><dt class="font-semibold">Status</dt><dd><span class="badge">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></dd></div>
            <div><dt class="font-semibold">Penggunaan</dt><dd>{{ $usageCount }} {{ $usageLabel }}</dd></div>
        </dl>
        <p>Kategori yang sudah digunakan tetap tampil pada data historis. Gunakan penonaktifan untuk menghentikan pilihan baru.</p>
        <div class="flex flex-wrap items-center gap-3">
            <a class="button" href="{{ route($routePrefix.'.edit', $category) }}">Edit kategori</a>
            <form method="POST" action="{{ route($routePrefix.'.update', $category) }}" data-confirm="{{ $category->is_active ? 'Nonaktifkan kategori ini? Histori tetap tersedia.' : 'Aktifkan kembali kategori ini?' }}">
                @csrf @method('PUT')
                <input type="hidden" name="category_name" value="{{ $category->category_name }}">
                <input type="hidden" name="description" value="{{ $category->description }}">
                <input type="hidden" name="is_active" value="{{ $category->is_active ? 0 : 1 }}">
                <button class="button-secondary" type="submit">{{ $category->is_active ? 'Nonaktifkan kategori' : 'Aktifkan kategori' }}</button>
            </form>
            @if ($usageCount === 0)
                <form method="POST" action="{{ route($routePrefix.'.destroy', $category) }}" data-confirm="Hapus kategori ini? Tindakan ini tidak dapat dibatalkan.">
                    @csrf @method('DELETE')
                    <button class="button-danger" type="submit">Hapus kategori</button>
                </form>
            @else
                <p>Kategori terpakai tidak dapat dihapus. Nonaktifkan bila tidak digunakan lagi.</p>
            @endif
        </div>
        <a href="{{ route($routePrefix.'.index') }}">Kembali ke daftar kategori</a>
    </div>
</x-layout>

