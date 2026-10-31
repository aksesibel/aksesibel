<x-layout :title="$record->exists ? 'Edit pengguna' : 'Tambah pengguna'">
    <form class="panel max-w-3xl space-y-5" method="POST" action="{{ $record->exists ? route('manage.users.update', $record) : route('manage.users.store') }}" data-confirm="Simpan data, peran, dan status akun pengguna ini?">
        @csrf @if($record->exists) @method('PUT') @endif
        @include('users.identity-fields', ['record' => $record])
        <div><label for="role_id">Peran</label><select class="field" id="role_id" name="role_id" required><option value="">Pilih peran</option>@foreach($roles as $role)<option value="{{ $role->role_id }}" @selected(old('role_id', $record->role_id) == $role->role_id)>{{ $role->role_name }}</option>@endforeach</select><p class="mt-1 text-sm text-slate-600">Hanya Pengelola Fasilitas aktif yang dapat mengelola modul ini. Admin berwenang memverifikasi laporan. Peran lama mengikuti keputusan integrasi kelompok.</p></div>
        <div><label for="account_status">Status akun</label><select class="field" id="account_status" name="account_status" required>@foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'] as $status => $label)<option value="{{ $status }}" @selected(old('account_status', $record->account_status ?? 'active') === $status)>{{ $label }}</option>@endforeach</select></div>
        <div class="flex gap-3"><button class="button" type="submit">Simpan pengguna</button><a class="button-secondary" href="{{ route('manage.users.index') }}">Batal</a></div>
    </form>
</x-layout>
