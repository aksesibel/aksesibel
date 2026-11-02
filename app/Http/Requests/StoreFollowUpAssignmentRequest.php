<?php

namespace App\Http\Requests;

use App\Models\Report;
use App\Models\User;
use App\Models\VerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFollowUpAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $roleName = $this->user()?->role?->role_name;

        return $roleName === 'Admin';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'report_id'        => ['required', 'integer', 'exists:reports,report_id'],
            'assigned_to'      => ['required', 'integer', 'exists:users,id'],
            'assignment_notes' => ['nullable', 'string', 'max:2000'],
            'due_date'         => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Validasi tambahan setelah rules dasar lolos.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $this->validateReportEligibility($validator);
                $this->validateAssigneeRole($validator);
            },
        ];
    }

    /**
     * Laporan harus berstatus "Dalam Tindak Lanjut".
     */
    private function validateReportEligibility(Validator $validator): void
    {
        if ($validator->errors()->has('report_id')) {
            return;
        }

        $report = Report::find($this->input('report_id'));
        if (! $report) {
            return;
        }

        $statusDalamTindakLanjut = VerificationStatus::where('status_name', 'Dalam Tindak Lanjut')->first();

        if (! $statusDalamTindakLanjut || $report->status_id !== $statusDalamTindakLanjut->status_id) {
            $validator->errors()->add(
                'report_id',
                'Laporan belum diteruskan ke tindak lanjut.'
            );
        }
    }

    /**
     * Petugas yang ditunjuk harus memiliki role "Petugas Tindak Lanjut" dan akun aktif.
     */
    private function validateAssigneeRole(Validator $validator): void
    {
        if ($validator->errors()->has('assigned_to')) {
            return;
        }

        $assignee = User::with('role')->find($this->input('assigned_to'));
        if (! $assignee) {
            return;
        }

        if ($assignee->role?->role_name !== 'Petugas Tindak Lanjut') {
            $validator->errors()->add(
                'assigned_to',
                'Pengguna yang ditunjuk bukan Petugas Tindak Lanjut.'
            );
        }

        if ($assignee->account_status !== 'active') {
            $validator->errors()->add(
                'assigned_to',
                'Akun petugas yang ditunjuk tidak aktif.'
            );
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'report_id.required'       => 'Laporan wajib dipilih.',
            'report_id.exists'         => 'Laporan tidak ditemukan.',
            'assigned_to.required'     => 'Petugas wajib dipilih.',
            'assigned_to.exists'       => 'Petugas tidak ditemukan.',
            'due_date.after_or_equal'  => 'Tenggat tidak boleh di masa lalu.',
            'assignment_notes.max'     => 'Catatan penugasan maksimal 2000 karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'report_id'        => 'laporan',
            'assigned_to'      => 'petugas',
            'assignment_notes' => 'catatan penugasan',
            'due_date'         => 'tenggat',
        ];
    }
}

