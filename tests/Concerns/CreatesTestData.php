<?php

namespace Tests\Concerns;

use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\Region;

/**
 * Helper pembuatan data uji: kategori, wilayah berjenjang, dan fasilitas.
 */
trait CreatesTestData
{
    protected function makeCategory(array $attributes = []): FacilityCategory
    {
        $suffix = uniqid();

        return FacilityCategory::create(array_merge([
            'category_name' => 'Transportasi Umum ' . $suffix,
            'slug'          => 'kategori-uji-' . $suffix,
            'icon'          => 'Bus',
            'is_active'     => true,
            'sort_order'    => 1,
        ], $attributes));
    }

    protected function makeRegion(string $level, string $name, ?int $parentId = null, ?string $code = null): Region
    {
        return Region::create([
            'code'      => $code ?? ('TEST-' . uniqid()),
            'name'      => $name,
            'level'     => $level,
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Rantai wilayah lengkap: Provinsi > Kota > Kecamatan > Kelurahan.
     *
     * @return array{province: Region, city: Region, district: Region, village: Region}
     */
    protected function makeRegionChain(): array
    {
        $suffix   = uniqid();
        $province = $this->makeRegion(Region::LEVEL_PROVINSI, 'Provinsi Uji', null, "99-{$suffix}");
        $city     = $this->makeRegion(Region::LEVEL_KOTA_KABUPATEN, 'Kota Uji', $province->region_id, "99.01-{$suffix}");
        $district = $this->makeRegion(Region::LEVEL_KECAMATAN, 'Kecamatan Uji', $city->region_id, "99.01.01-{$suffix}");
        $village  = $this->makeRegion(Region::LEVEL_KELURAHAN, 'Kelurahan Uji', $district->region_id, "99.01.01.1001-{$suffix}");

        return compact('province', 'city', 'district', 'village');
    }

    protected function makeFacility(array $attributes = []): Facility
    {
        if (!isset($attributes['category_id'])) {
            $attributes['category_id'] = $this->makeCategory()->category_id;
        }

        return Facility::create(array_merge([
            'name'                 => 'Fasilitas Uji',
            'description'          => 'Deskripsi fasilitas uji.',
            'address'              => 'Jl. Uji No. 1',
            'accessibility_status' => Facility::STATUS_AKSES_PENUH,
        ], $attributes));
    }
}
