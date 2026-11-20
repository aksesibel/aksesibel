<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'min:3', 'max:120'],
            'description'          => ['required', 'string', 'max:1000'],
            'category_id'          => ['required', 'exists:facility_categories,category_id'],
            'address'              => ['required', 'string', 'max:500'],
            'city'                 => ['required', 'string', 'max:100'],
            'phone'                => ['nullable', 'regex:/^(\+62|62|0)[0-9]{7,13}$/'],
            'email'                => ['nullable', 'email', 'max:255'],
            'accessibility_status' => ['required', 'in:AKSES_PENUH,AKSES_SEBAGIAN,BELUM_AKSESIBEL,MENUNGGU_KONFIRMASI'],
            'operating_hours'      => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'Nama fasilitas wajib diisi.',
            'name.min'                      => 'Nama fasilitas minimal 3 karakter.',
            'name.max'                      => 'Nama fasilitas maksimal 120 karakter.',
            'description.required'          => 'Deskripsi wajib diisi.',
            'description.max'               => 'Deskripsi maksimal 1.000 karakter.',
            'category_id.required'          => 'Kategori wajib dipilih.',
            'category_id.exists'            => 'Kategori yang dipilih tidak valid.',
            'address.required'              => 'Alamat wajib diisi.',
            'city.required'                 => 'Kota wajib diisi.',
            'phone.regex'                   => 'Format nomor telepon tidak valid. Contoh: 08123456789 atau +6221XXXX.',
            'email.email'                   => 'Format email tidak valid.',
            'accessibility_status.required' => 'Status aksesibilitas wajib dipilih.',
            'accessibility_status.in'       => 'Status aksesibilitas yang dipilih tidak valid.',
        ];
    }
}
