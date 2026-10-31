<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateFacilityManager extends Command
{
    protected $signature = 'aksesibel:create-manager';

    protected $description = 'Buat akun Pengelola Fasilitas baru secara interaktif tanpa kredensial tetap';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Jalankan perintah secara interaktif. Password tidak diterima melalui argumen atau environment.');

            return self::FAILURE;
        }

        $role = Role::query()->where('role_name', Role::FACILITY_MANAGER)->get()
            ->first(fn (Role $role) => $role->role_name === Role::FACILITY_MANAGER);

        if (! $role) {
            $this->error('Jalankan php artisan db:seed --class=MasterDataSeeder terlebih dahulu.');

            return self::FAILURE;
        }

        $data = [
            'name' => trim((string) $this->ask('Nama pengelola')),
            'email' => trim((string) $this->ask('Email')),
            'password' => $this->secret('Password (minimal 12 karakter)', false),
            'password_confirmation' => $this->secret('Konfirmasi password', false),
        ];
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', Password::min(12), 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $validated = $validator->safe()->only(['name', 'email', 'password']);
        try {
            User::create([...$validated, 'role_id' => $role->role_id, 'account_status' => 'active']);
        } catch (UniqueConstraintViolationException) {
            $this->error('Email sudah digunakan. Akun lama tidak diubah.');

            return self::FAILURE;
        }
        $this->info('Akun Pengelola Fasilitas aktif berhasil dibuat. Akun lama tidak diubah.');

        return self::SUCCESS;
    }
}
