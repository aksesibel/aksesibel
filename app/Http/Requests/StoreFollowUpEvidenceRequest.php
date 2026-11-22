<?php

namespace App\Http\Requests;

use App\Models\FollowUpAssignment;
use App\Models\FollowUpProgress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFollowUpEvidenceRequest extends FormRequest
{
    /**
     * Otorisasi: Admin, Pengelola Fasilitas, atau Petugas yang ditugaskan (akun aktif).
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

        if (in_array($roleName, ['Admin', 'Pengelola Fasilitas'], true)) {
            return true;
        }

        return $roleName === 'Petugas Tindak Lanjut'
            && (int) $assignment->assigned_to === (int) $user->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'progress_id' => ['required', 'integer', 'exists:follow_up_progress,progress_id'],
            'photos'      => ['required', 'array', 'min:1', 'max:5'],
            'photos.*'    => ['file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pastikan progress_id benar-benar milik penugasan ini.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                /** @var FollowUpAssignment|null $assignment */
                $assignment = $this->route('follow_up_assignment');
                if (! $assignment || $validator->errors()->has('progress_id')) {
                    return;
                }

                $progressId = (int) $this->input('progress_id');
                $belongs = FollowUpProgress::where('progress_id', $progressId)
                    ->where('assignment_id', $assignment->assignment_id)
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add(
                        'progress_id',
                        'Entri progres yang dipilih tidak sesuai dengan penugasan ini.'
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'progress_id.required'  => 'Pilih entri progres tujuan bukti.',
            'progress_id.exists'    => 'Entri progres tidak ditemukan.',
            'photos.required'       => 'Pilih minimal satu foto.',
            'photos.min'            => 'Pilih minimal satu foto.',
            'photos.max'            => 'Maksimal 5 foto per pengiriman.',
            'photos.*.mimes'        => 'Format foto harus JPG atau PNG.',
            'photos.*.max'          => 'Ukuran setiap foto maksimal 5 MB.',
            'description.max'       => 'Deskripsi maksimal 500 karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'progress_id' => 'entri progres',
            'photos'      => 'foto bukti',
            'photos.*'    => 'foto bukti',
            'description' => 'deskripsi bukti',
        ];
    }
}
