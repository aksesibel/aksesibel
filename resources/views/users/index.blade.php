<x-layout title="Pengguna dan Hak Akses" subtitle="Kelola akun dan kewenangan pengguna untuk menjaga layanan aksesibilitas tetap berjalan.">
    <x-slot:actions><a class="button" href="{{ route('manage.users.create') }}"><x-icon name="plus" />Tambah pengguna</a></x-slot:actions>
    <section class="panel">
    <div class="section-heading"><div><h2>Pengguna dan kewenangan</h2><p>Kelola peran dan status akun. Histori pengguna tetap disimpan.</p></div><span class="badge">{{ number_format($users->total(), 0, ',', '.') }} pengguna</span></div>
    <form class="form-grid filter-panel mb-6" method="GET" action="{{ route('manage.users.index') }}">
        <div><label for="q">Cari nama atau email</label><input class="field" id="q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="255"></div>
        <div><label for="role_id">Peran</label><select class="field" id="role_id" name="role_id"><option value="">Semua peran</option>@foreach($roles as $role)<option value="{{ $role->role_id }}" @selected(($filters['role_id'] ?? '') == $role->role_id)>{{ $role->role_name }}</option>@endforeach</select></div>
        <div><label for="account_status">Status akun</label><select class="field" id="account_status" name="account_status"><option value="">Semua status</option>@foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'] as $status => $label)<option value="{{ $status }}" @selected(($filters['account_status'] ?? '') === $status)>{{ $label }}</option>@endforeach</select></div>
        <div class="flex items-end gap-2"><button class="button" type="submit">Terapkan</button><a class="button-secondary" href="{{ route('manage.users.index') }}">Reset filter</a></div>
    </form>
    <div class="table-wrap"><table><caption class="sr-only">Daftar pengguna, peran, dan status akun</caption><thead><tr><th scope="col">Nama / Email</th><th scope="col">Peran</th><th scope="col">Status akun</th><th scope="col">Tindakan</th></tr></thead><tbody>
        @forelse($users as $record)<tr><td><span class="font-semibold">{{ $record->name }}</span><br><span class="text-xs text-slate-500">{{ $record->email }}</span></td><td>{{ $record->role?->role_name ?? 'Belum memiliki peran' }}</td><td><span class="badge" data-state="{{ $record->account_status }}">{{ ['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'][$record->account_status] ?? 'Status tidak dikenal' }}</span></td><td><a class="inline-flex items-center gap-2" href="{{ route('manage.users.show', $record) }}">Detail<span class="sr-only"> {{ $record->name }}</span><x-icon name="arrow" :size="16" /></a></td></tr>
        @empty<tr><td colspan="4">Tidak ada pengguna yang sesuai dengan filter.</td></tr>@endforelse
    </tbody></table></div><div class="table-footer"><p>{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna</p><div>{{ $users->links() }}</div></div>
    </section>
</x-layout>
