<x-layout title="Detail pengguna">
    <section class="panel space-y-5">
        <dl class="grid gap-4 sm:grid-cols-2"><div><dt class="text-sm text-slate-500">Nama</dt><dd class="font-semibold">{{ $record->name }}</dd></div><div><dt class="text-sm text-slate-500">Email</dt><dd>{{ $record->email }}</dd></div><div><dt class="text-sm text-slate-500">Peran</dt><dd>{{ $record->role?->role_name ?? 'Belum memiliki peran' }}</dd></div><div><dt class="text-sm text-slate-500">Status</dt><dd>{{ ['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'][$record->account_status] ?? 'Status tidak dikenal' }}</dd></div></dl>
        <div class="flex flex-wrap gap-3"><a class="button" href="{{ route('manage.users.edit', $record) }}">Edit pengguna</a><a class="button-secondary" href="{{ route('manage.users.index') }}">Kembali</a></div>
    </section>
    <form class="panel max-w-2xl space-y-4" action="{{ route('manage.users.status', $record) }}" method="POST" data-confirm="Ubah status akun ini? Akun nonaktif atau ditangguhkan akan kehilangan akses, termasuk sesi yang sedang berjalan.">
        @csrf @method('PATCH')
        <h2 class="text-lg font-semibold">Ubah status akun</h2>
        <p class="text-sm text-slate-600">Gunakan penonaktifan untuk mempertahankan histori laporan dan aktivitas. Akun sendiri dan pengelola aktif terakhir dilindungi.</p>
        <label for="account_status">Status akun</label><select class="field" name="account_status" id="account_status" required>@foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'] as $status => $label)<option value="{{ $status }}" @selected($record->account_status === $status)>{{ $label }}</option>@endforeach</select>
        <button class="button-secondary" type="submit">Ubah status</button>
    </form>
</x-layout>
