<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-supporting-data') ?? false;
    }

    public function rules(): array
    {
        return [
            'category_name' => ['required', 'string', 'max:255', Rule::unique('issue_categories', 'category_name')->ignore($this->route('issue_category'))],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.string' => 'Nama kategori harus berupa teks.',
            'category_name.max' => 'Nama kategori maksimal 255 karakter.',
            'category_name.unique' => 'Nama kategori sudah digunakan.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'is_active.required' => 'Status kategori wajib dipilih.',
            'is_active.boolean' => 'Status kategori harus aktif atau nonaktif.',
        ];
    }
}
