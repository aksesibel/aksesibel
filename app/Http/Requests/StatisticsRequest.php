<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StatisticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-supporting-data') ?? false;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        $rules = ['page' => ['nullable', 'integer', 'min:1']];

        foreach ([
            'period_id' => ['reporting_periods', 'period_id'],
            'category_id' => ['facility_categories', 'category_id'],
            'issue_category_id' => ['issue_categories', 'issue_category_id'],
        ] as $field => [$table, $key]) {
            $rules[$field] = ['bail', 'nullable', 'integer', 'min:1'];

            if (Schema::hasTable($table)) {
                $rules[$field][] = Rule::exists($table, $key);
            }
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            '*.integer' => ':attribute harus berupa angka bulat.',
            '*.min' => ':attribute tidak valid.',
            '*.exists' => ':attribute yang dipilih tidak tersedia.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'period_id' => 'Periode pelaporan',
            'category_id' => 'Kategori fasilitas',
            'issue_category_id' => 'Kategori kendala',
            'page' => 'Halaman',
        ];
    }
}
