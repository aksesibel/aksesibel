<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportingPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-supporting-data') ?? false;
    }

    public function rules(): array
    {
        return [
            'period_name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'period_name.required' => 'Nama periode wajib diisi.',
            'period_name.string' => 'Nama periode harus berupa teks.',
            'period_name.max' => 'Nama periode maksimal 255 karakter.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date_format' => 'Tanggal mulai harus berupa tanggal yang valid (YYYY-MM-DD).',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.date_format' => 'Tanggal selesai harus berupa tanggal yang valid (YYYY-MM-DD).',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau sesudah tanggal mulai.',
        ];
    }
}
