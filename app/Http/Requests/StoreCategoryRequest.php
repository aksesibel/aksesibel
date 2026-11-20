<?php

namespace App\Http\Requests;

use App\Models\FacilityCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_name' => ['required', 'string', 'min:3', 'max:60', Rule::unique('facility_categories', 'category_name')],
            'slug'          => ['required', 'string', 'min:3', 'max:80', 'regex:/^[a-z0-9-]+$/', Rule::unique('facility_categories', 'slug')],
            'description'   => ['nullable', 'string', 'max:300'],
            'icon'          => ['required', 'string', Rule::in(array_keys(FacilityCategory::ALLOWED_ICONS))],
            'is_active'     => ['boolean'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.min'      => 'Nama kategori minimal 3 karakter.',
            'category_name.max'      => 'Nama kategori maksimal 60 karakter.',
            'category_name.unique'   => 'Nama kategori sudah digunakan.',
            'slug.required'          => 'Slug wajib diisi.',
            'slug.unique'            => 'Slug sudah digunakan oleh kategori lain.',
            'slug.regex'             => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung (-).',
            'description.max'        => 'Deskripsi maksimal 300 karakter.',
            'icon.required'          => 'Ikon wajib dipilih.',
            'icon.in'                => 'Ikon yang dipilih tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active'  => $this->boolean('is_active', true),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
