<x-layout title="Pengguna dan Hak Akses">
    <div class="flex flex-wrap items-center justify-between gap-4"><p class="text-slate-600">Kelola peran dan status akun. Histori pengguna tetap disimpan.</p><a class="button" href="{{ route('manage.users.create') }}">Tambah pengguna</a></div>
    <form class="panel form-grid" method="GET" action="{{ route('manage.users.index') }}">
        <div><label for="q">Cari nama atau email</label><input class="field" id="q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="255"></div>
        <div><label for="role_id">Peran</label><select class="field" id="role_id" name="role_id"><option value="">Semua peran</option>@foreach($roles as $role)<option value="{{ $role->role_id }}" @selected(($filters['role_id'] ?? '') == $role->role_id)>{{ $role->role_name }}</option>@endforeach</select></div>
        <div><label for="account_status">Status akun</label><select class="field" id="account_status" name="account_status"><option value="">Semua status</option>@foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'] as $status => $label)<option value="{{ $status }}" @selected(($filters['account_status'] ?? '') === $status)>{{ $label }}</option>@endforeach</select></div>
        <div class="flex items-end gap-2"><button class="button" type="submit">Terapkan</button><a class="button-secondary" href="{{ route('manage.users.index') }}">Reset filter</a></div>
    </form>
    <div class="panel"><div class="table-wrap"><table><caption class="sr-only">Daftar pengguna, peran, dan status akun</caption><thead><tr><th scope="col">Nama / Email</th><th scope="col">Peran</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
        @forelse($users as $record)<tr><td><span class="font-semibold">{{ $record->name }}</span><br><span class="text-sm text-slate-600">{{ $record->email }}</span></td><td>{{ $record->role?->role_name ?? 'Belum memiliki peran' }}</td><td><span class="badge">{{ ['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'][$record->account_status] ?? 'Status tidak dikenal' }}</span></td><td><a class="text-teal-800 underline" href="{{ route('manage.users.show', $record) }}">Detail<span class="sr-only"> {{ $record->name }}</span></a></td></tr>
        @empty<tr><td colspan="4">Tidak ada pengguna yang sesuai dengan filter.</td></tr>@endforelse
    </tbody></table></div><div class="mt-5">{{ $users->links() }}</div></div>
</x-layout>
