<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

/**
 * Halaman publik /fasilitas: pencarian wilayah, tampilan wilayah pada kartu,
 * dan pengurutan berdasarkan jarak.
 */
class PublicFacilityListTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_pencarian_mencocokkan_nama_wilayah(): void
    {
        $chain = $this->makeRegionChain();

        $this->makeFacility([
            'name'           => 'Fasilitas Di Kelurahan Uji',
            'region_id'      => $chain['village']->region_id,
            'district_id'    => $chain['district']->region_id,
            'city_region_id' => $chain['city']->region_id,
            'province_id'    => $chain['province']->region_id,
        ]);
        $this->makeFacility(['name' => 'Fasilitas Di Tempat Lain']);

        $this->get(route('facilities.index', ['q' => 'Kelurahan Uji']))
            ->assertOk()
            ->assertSee('Fasilitas Di Kelurahan Uji')
            ->assertDontSee('Fasilitas Di Tempat Lain');
    }

    public function test_kartu_menampilkan_kelurahan_dan_kota(): void
    {
        $chain = $this->makeRegionChain();

        $this->makeFacility([
            'name'           => 'Fasilitas Wilayah',
            'region_id'      => $chain['village']->region_id,
            'district_id'    => $chain['district']->region_id,
            'city_region_id' => $chain['city']->region_id,
            'province_id'    => $chain['province']->region_id,
        ]);

        $this->get(route('facilities.index'))
            ->assertOk()
            ->assertSee('Kelurahan Uji')
            ->assertSee('Kota Uji');
    }

    public function test_pengurutan_berdasarkan_jarak_menampilkan_jarak_dan_urutan_terdekat(): void
    {
        $this->makeFacility(['name' => 'Fasilitas Dekat',  'latitude' => -6.201, 'longitude' => 106.801]);
        $this->makeFacility(['name' => 'Fasilitas Jauh',   'latitude' => -6.9,   'longitude' => 107.6]);

        $response = $this->get(route('facilities.index', [
            'lat'  => -6.2,
            'lng'  => 106.8,
            'sort' => 'jarak',
        ]));

        $response->assertOk()
            ->assertSee('km dari Anda')
            ->assertSeeInOrder(['Fasilitas Dekat', 'Fasilitas Jauh']);
    }

    public function test_filter_wilayah_berjenjang_menyaring_hasil(): void
    {
        $chain = $this->makeRegionChain();

        $this->makeFacility(['name' => 'Fasilitas Kota Uji', 'city_region_id' => $chain['city']->region_id]);
        $this->makeFacility(['name' => 'Fasilitas Kota Lain']);

        $this->get(route('facilities.index', ['city_id' => $chain['city']->region_id]))
            ->assertOk()
            ->assertSee('Fasilitas Kota Uji')
            ->assertDontSee('Fasilitas Kota Lain');
    }
}
