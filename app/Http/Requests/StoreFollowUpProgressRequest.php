<?php

namespace App\Http\Requests;

use App\Models\FollowUpAssignment;
use App\Models\FollowUpProgress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFollowUpProgressRequest extends FormRequest
{
    /**
     * Otorisasi: Pengelola Fasilitas, Admin, atau Petugas yang ditugaskan (dengan akun aktif).
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

        // Admin dan Pengelola Fasilitas berhak mencatat supervisi/progres
        if (in_array($roleName, ['Admin', 'Pengelola Fasilitas'], true)) {
            return true;
        }

        // Petugas Lapangan hanya berhak menambah jika penugasan memang diamanahkan kepadanya
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
            'progress_status' => ['required', 'string', Rule::in(FollowUpProgress::STATUS_OPTIONS)],
            'action_description' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    /**
     * Validasi kelayakan penugasan dan laporan sebelum progres dicatat.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                /** @var FollowUpAssignment|null $assignment */
                $assignment = $this->route('follow_up_assignment');
                if (! $assignment) {
                    return;
                }

                // 1. Penugasan yang dibatalkan tidak boleh menerima pembaruan progres
                if (in_array(strtolower((string) $assignment->assignment_status), ['cancelled', 'dibatalkan'], true)) {
                    $validator->errors()->add(
                        'assignment_status',
                        'Penugasan ini telah dibatalkan dan tidak dapat menerima pembaruan progres.'
                    );
                }

                // 2. Laporan terkait harus berstatus "Dalam Tindak Lanjut" (telah diteruskan)
                $report = $assignment->report;
                if (! $report || $report->status?->status_name !== 'Dalam Tindak Lanjut') {
                    $validator->errors()->add(
                        'report_status',
                        'Laporan terkait belum diteruskan atau tidak dalam status tindak lanjut yang sah.'
                    );
                }
            },
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
            'progress_status.required' => 'Status progres wajib dipilih.',
            'progress_status.in' => 'Pilihan status progres tidak valid.',
            'action_description.required' => 'Uraian tindakan wajib diisi.',
            'action_description.min' => 'Uraian tindakan minimal 5 karakter.',
            'action_description.max' => 'Uraian tindakan maksimal 2.000 karakter.',
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
            'progress_status' => 'status progres',
            'action_description' => 'uraian tindakan',
        ];
    }
}
