<x-layout :title="$record->exists ? 'Edit pengguna' : 'Tambah pengguna'">
    <form class="panel editor-panel" method="POST" action="{{ $record->exists ? route('manage.users.update', $record) : route('manage.users.store') }}" data-confirm="Simpan data, peran, dan status akun pengguna ini?">
        @csrf @if($record->exists) @method('PUT') @endif
        <div class="form-heading"><h2>Informasi pengguna</h2><p>Lengkapi identitas, peran, dan status akun pengguna.</p></div>
        <div class="form-grid editor-grid">
        @include('users.identity-fields', ['record' => $record])
        <div><label for="role_id">Peran</label><select class="field" id="role_id" name="role_id" required><option value="">Pilih peran</option>@foreach($roles as $role)<option value="{{ $role->role_id }}" @selected(old('role_id', $record->role_id) == $role->role_id)>{{ $role->role_name }}</option>@endforeach</select><p class="mt-1 text-sm text-slate-600">Gunakan peran Masyarakat, Admin, atau Pengelola Fasilitas. Hanya Pengelola Fasilitas aktif yang dapat mengelola modul ini. Admin berwenang memverifikasi laporan.</p>
            @if ($record->exists && ! in_array($record->role?->role_name, \App\Models\Role::PROPOSAL_ROLES, true))
                <p>Peran saat ini: {{ $record->role?->role_name ?? 'Belum ditentukan' }}. Pilih salah satu dari tiga peran sebelum menyimpan perubahan.</p>
            @endif
        </div>
        <div><label for="account_status">Status akun</label><select class="field" id="account_status" name="account_status" required>@foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'] as $status => $label)<option value="{{ $status }}" @selected(old('account_status', $record->account_status ?? 'active') === $status)>{{ $label }}</option>@endforeach</select></div>
        </div>
        <div class="information-note mb-6"><x-icon name="shield" /><p>Peran menentukan menu dan tindakan yang tersedia. Penonaktifan akun mempertahankan histori laporan dan aktivitas pengguna.</p></div>
        <div class="flex flex-wrap gap-3"><button class="button" type="submit">Simpan pengguna</button><a class="button-secondary" href="{{ route('manage.users.index') }}">Batal</a></div>
    </form>
</x-layout>
