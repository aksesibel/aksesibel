<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-supporting-data') ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'role_id')->whereIn('role_name', Role::PROPOSAL_ROLES)],
            'account_status' => ['required', Rule::in(User::STATUSES)],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::min(12), 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.exists' => 'Pilih peran Masyarakat, Admin, atau Pengelola Fasilitas.',
        ];
    }
}
