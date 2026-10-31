<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserManagement
{
    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data): User
    {
        return $this->persist($actor, null, $data);
    }

    /** @param array<string, mixed> $data */
    public function update(User $actor, User $target, array $data): User
    {
        return $this->persist($actor, $target, $data);
    }

    /** @param array<string, mixed> $data */
    private function persist(User $actor, ?User $target, array $data): User
    {
        try {
            return DB::transaction(function () use ($actor, $target, $data): User {
                /**
                 * Every managed write locks the same role rows first. The mutex
                 * serializes two managers demoting each other under InnoDB.
                 * Strict PHP matching keeps the count identical to the gate,
                 * including on a case-insensitive database collation.
                 */
                $roles = Role::query()->orderBy('role_id')->lockForUpdate()->get();
                $managerRoleIds = $roles->whereStrict('role_name', Role::FACILITY_MANAGER)->modelKeys();
                $activeManagers = User::query()->whereIn('role_id', $managerRoleIds)
                    ->where('account_status', 'active')->orderBy('id')->lockForUpdate()->get();
                $activeManagers = $activeManagers->whereStrict('account_status', 'active');
                $currentActor = User::query()->lockForUpdate()->findOrFail($actor->id);
                $currentActor->setRelation('role', $roles->firstWhere('role_id', $currentActor->role_id));

                if (! $currentActor->canManageSupportingData()) {
                    throw new AuthorizationException('Hak pengelolaan akun Anda telah berubah.');
                }

                if ($target === null) {
                    return User::create($data);
                }

                $current = User::query()->lockForUpdate()->findOrFail($target->id);
                $nextStatus = $data['account_status'] ?? $current->account_status;
                $nextRole = (int) ($data['role_id'] ?? $current->role_id);

                if ($current->id === $actor->id && $nextStatus !== 'active') {
                    throw ValidationException::withMessages(['account_status' => 'Anda tidak dapat menonaktifkan atau menangguhkan akun sendiri.']);
                }

                $wasManager = in_array((int) $current->role_id, $managerRoleIds, true) && $current->account_status === 'active';
                $remainsManager = in_array($nextRole, $managerRoleIds, true) && $nextStatus === 'active';

                if ($wasManager && ! $remainsManager && $activeManagers->count() <= 1) {
                    throw ValidationException::withMessages(['role_id' => 'Harus tersedia minimal satu Pengelola Fasilitas aktif.', 'account_status' => 'Pengelola Fasilitas aktif terakhir tidak dapat dinonaktifkan atau diubah perannya.']);
                }

                if (empty($data['password'])) {
                    unset($data['password']);
                }

                $current->update($data);

                return $current;
            }, 5);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['email' => 'Email sudah digunakan pengguna lain.']);
        }
    }
}
