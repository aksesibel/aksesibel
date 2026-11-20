<?php

namespace App\Http\Requests;

use App\Models\Region;
use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'min:3', 'max:120'],
            'description'          => ['required', 'string', 'max:1000'],
            'category_id'          => ['required', 'exists:facility_categories,category_id'],
            'address'              => ['required', 'string', 'max:500'],
            'city'                 => ['nullable', 'string', 'max:100'],
            'postal_code'          => ['nullable', 'string', 'regex:/^\d{5}$/'],
            // Wilayah berjenjang
            'province_id'          => ['nullable', 'exists:regions,region_id'],
            'city_region_id'       => ['nullable', 'exists:regions,region_id'],
            'district_id'          => ['nullable', 'exists:regions,region_id'],
            'region_id'            => ['nullable', 'exists:regions,region_id'],
            // Koordinat — keduanya harus diisi bersamaan atau dikosongkan bersamaan
            'latitude'             => ['nullable', 'numeric', 'min:-90', 'max:90', 'required_with:longitude'],
            'longitude'            => ['nullable', 'numeric', 'min:-180', 'max:180', 'required_with:latitude'],
            'coordinate_source'    => ['nullable', 'in:MANUAL,PIN_PETA,GEOKODING,LOKASI_PERANGKAT'],
            // Info lokasi
            'location_note'        => ['nullable', 'string', 'max:300'],
            'entrance_note'        => ['nullable', 'string', 'max:300'],
            'nearby_transit'       => ['nullable', 'string', 'max:300'],
            // Kontak & status
            'phone'                => ['nullable', 'regex:/^(\+62|62|0)[0-9]{7,13}$/'],
            'email'                => ['nullable', 'email', 'max:255'],
            'accessibility_status' => ['required', 'in:AKSES_PENUH,AKSES_SEBAGIAN,BELUM_AKSESIBEL,MENUNGGU_KONFIRMASI'],
            'operating_hours'      => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                  => 'Nama fasilitas wajib diisi.',
            'name.min'                       => 'Nama fasilitas minimal 3 karakter.',
            'name.max'                       => 'Nama fasilitas maksimal 120 karakter.',
            'description.required'           => 'Deskripsi wajib diisi.',
            'description.max'                => 'Deskripsi maksimal 1.000 karakter.',
            'category_id.required'           => 'Kategori wajib dipilih.',
            'category_id.exists'             => 'Kategori yang dipilih tidak valid.',
            'address.required'               => 'Alamat wajib diisi.',
            'postal_code.regex'              => 'Kode pos harus 5 digit angka.',
            'latitude.required_with'         => 'Latitude wajib diisi jika longitude diisi.',
            'latitude.min'                   => 'Latitude harus antara -90 dan 90.',
            'latitude.max'                   => 'Latitude harus antara -90 dan 90.',
            'longitude.required_with'        => 'Longitude wajib diisi jika latitude diisi.',
            'longitude.min'                  => 'Longitude harus antara -180 dan 180.',
            'longitude.max'                  => 'Longitude harus antara -180 dan 180.',
            'phone.regex'                    => 'Format nomor telepon tidak valid. Contoh: 08123456789.',
            'email.email'                    => 'Format email tidak valid.',
            'accessibility_status.required'  => 'Status aksesibilitas wajib dipilih.',
            'accessibility_status.in'        => 'Status aksesibilitas yang dipilih tidak valid.',
        ];
    }

    /**
     * Peringatan koordinat di luar Indonesia + validasi konsistensi hierarki wilayah.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $this->checkCoordinateWarning($v);
            $this->checkRegionHierarchy($v);
        });
    }

    /**
     * Peringatan (bukan error) jika koordinat di luar kisaran wilayah Indonesia.
     */
    private function checkCoordinateWarning($v): void
    {
        $lat = $this->input('latitude');
        $lng = $this->input('longitude');

        if ($lat === null || $lat === '' || $lng === null || $lng === '') {
            return;
        }

        $bbox = config('aksesibel.indonesia_bbox');

        if ((float) $lat < $bbox['minLat'] || (float) $lat > $bbox['maxLat']
            || (float) $lng < $bbox['minLng'] || (float) $lng > $bbox['maxLng']) {
            session()->flash('coord_warning', 'Koordinat yang dimasukkan berada di luar wilayah Indonesia. Pastikan koordinat sudah benar.');
        }
    }

    /**
     * Hierarki wilayah harus konsisten: kelurahan milik kecamatan yang dipilih,
     * kecamatan milik kota/kabupaten, dan kota/kabupaten milik provinsi.
     */
    private function checkRegionHierarchy($v): void
    {
        $provinceId = $this->input('province_id');
        $cityId     = $this->input('city_region_id');
        $districtId = $this->input('district_id');
        $villageId  = $this->input('region_id');

        if ($cityId) {
            $city = Region::find($cityId);
            if (!$city || $city->level !== Region::LEVEL_KOTA_KABUPATEN) {
                $v->errors()->add('city_region_id', 'Kota/Kabupaten yang dipilih tidak valid.');
            } elseif ($provinceId && (int) $city->parent_id !== (int) $provinceId) {
                $v->errors()->add('city_region_id', 'Kota/Kabupaten tidak berada di provinsi yang dipilih.');
            }
        }

        if ($districtId) {
            $district = Region::find($districtId);
            if (!$district || $district->level !== Region::LEVEL_KECAMATAN) {
                $v->errors()->add('district_id', 'Kecamatan yang dipilih tidak valid.');
            } elseif ($cityId && (int) $district->parent_id !== (int) $cityId) {
                $v->errors()->add('district_id', 'Kecamatan tidak berada di kota/kabupaten yang dipilih.');
            }
        }

        if ($villageId) {
            $village = Region::find($villageId);
            if (!$village || $village->level !== Region::LEVEL_KELURAHAN) {
                $v->errors()->add('region_id', 'Kelurahan yang dipilih tidak valid.');
            } elseif ($districtId && (int) $village->parent_id !== (int) $districtId) {
                $v->errors()->add('region_id', 'Kelurahan tidak berada di kecamatan yang dipilih.');
            }
        }
    }
}
