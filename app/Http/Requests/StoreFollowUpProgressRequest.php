<?php

namespace App\Http\Requests;

use App\Models\FollowUpAssignment;
use Illuminate\Foundation\Http\FormRequest;

class StoreFollowUpProgressRequest extends FormRequest
{
    /**
     * Otorisasi: Hanya Petugas penanggung jawab yang aktif atau Admin yang berhak menambah progres.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || $user->account_status !== 'active') {
            return false;
        }

        $user->loadMissing('role');

        /** @var FollowUpAssignment|null $assignment */
        $assignment = $this->route('follow_up_assignment');
        if (! $assignment) {
            return false;
        }

        $roleName = $user->role?->role_name;

        // Admin diizinkan mencatat supervisi/progres
        if ($roleName === 'Admin') {
            return true;
        }

        // Petugas hanya boleh menambah jika penugasan memang diamanahkan kepadanya
        return $roleName === 'Petugas Tindak Lanjut' && (int) $assignment->assigned_to === (int) $user->id;
    }

    /**
     * Aturan validasi penyimpanan progres penanganan.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'progress_status'    => ['required', 'string', 'max:100'],
            'action_description' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    /**
     * Pesan kustom validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'progress_status.required'    => 'Status progres wajib dipilih atau diisi.',
            'progress_status.max'         => 'Status progres maksimal 100 karakter.',
            'action_description.required' => 'Uraian tindakan wajib diisi.',
            'action_description.min'      => 'Uraian tindakan minimal 5 karakter.',
            'action_description.max'      => 'Uraian tindakan maksimal 2.000 karakter.',
        ];
    }

    /**
     * Nama atribut untuk pesan validasi.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'progress_status'    => 'status progres',
            'action_description' => 'uraian tindakan',
        ];
    }
}

