<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

/**
 * Validasi lokasi pada form tambah/ubah fasilitas (admin).
 */
class FacilityLocationValidationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name'                 => 'Fasilitas Lokasi Uji',
            'description'          => 'Deskripsi uji lokasi.',
            'category_id'          => $this->makeCategory()->category_id,
            'address'              => 'Jl. Uji No. 10',
            'accessibility_status' => Facility::STATUS_AKSES_PENUH,
        ], $overrides);
    }

    public function test_latitude_di_luar_rentang_ditolak(): void
    {
        $this->post(route('admin.facilities.store'), $this->payload([
            'latitude'  => 95,
            'longitude' => 106.8,
        ]))->assertSessionHasErrors('latitude');

        $this->assertDatabaseCount('facilities', 0);
    }

    public function test_longitude_di_luar_rentang_ditolak(): void
    {
        $this->post(route('admin.facilities.store'), $this->payload([
            'latitude'  => -6.2,
            'longitude' => 200,
        ]))->assertSessionHasErrors('longitude');

        $this->assertDatabaseCount('facilities', 0);
    }

    public function test_hanya_latitude_diisi_ditolak(): void
    {
        $this->post(route('admin.facilities.store'), $this->payload([
            'latitude' => -6.2,
        ]))->assertSessionHasErrors('longitude');

        $this->assertDatabaseCount('facilities', 0);
    }

    public function test_hanya_longitude_diisi_ditolak(): void
    {
        $this->post(route('admin.facilities.store'), $this->payload([
            'longitude' => 106.8,
        ]))->assertSessionHasErrors('latitude');

        $this->assertDatabaseCount('facilities', 0);
    }

    public function test_kode_pos_bukan_5_digit_ditolak(): void
    {
        $this->post(route('admin.facilities.store'), $this->payload([
            'postal_code' => '12',
        ]))->assertSessionHasErrors('postal_code');

        $this->post(route('admin.facilities.store'), $this->payload([
            'postal_code' => 'ABCDE',
        ]))->assertSessionHasErrors('postal_code');

        $this->assertDatabaseCount('facilities', 0);
    }

    public function test_kode_pos_5_digit_diterima(): void
    {
        $this->post(route('admin.facilities.store'), $this->payload([
            'postal_code' => '10110',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('facilities', 1);
    }

    public function test_hierarki_wilayah_tidak_konsisten_ditolak(): void
    {
        $chain         = $this->makeRegionChain();
        $otherDistrict = $this->makeRegion(Region::LEVEL_KECAMATAN, 'Kecamatan Lain', $chain['city']->region_id);
        $otherVillage  = $this->makeRegion(Region::LEVEL_KELURAHAN, 'Kelurahan Lain', $otherDistrict->region_id);

        // Kelurahan berasal dari kecamatan lain → hierarki tidak konsisten
        $this->post(route('admin.facilities.store'), $this->payload([
            'province_id'    => $chain['province']->region_id,
            'city_region_id' => $chain['city']->region_id,
            'district_id'    => $chain['district']->region_id,
            'region_id'      => $otherVillage->region_id,
        ]))->assertSessionHasErrors('region_id');

        $this->assertDatabaseCount('facilities', 0);
    }

    public function test_hierarki_wilayah_konsisten_diterima(): void
    {
        $chain = $this->makeRegionChain();

        $this->post(route('admin.facilities.store'), $this->payload([
            'province_id'    => $chain['province']->region_id,
            'city_region_id' => $chain['city']->region_id,
            'district_id'    => $chain['district']->region_id,
            'region_id'      => $chain['village']->region_id,
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('facilities', 1);
    }

    public function test_koordinat_di_luar_indonesia_hanya_memberi_peringatan(): void
    {
        $response = $this->post(route('admin.facilities.store'), $this->payload([
            'latitude'  => 10,
            'longitude' => 50,
        ]));

        // Bukan error — fasilitas tetap tersimpan, tetapi diberi peringatan.
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('coord_warning');
        $this->assertDatabaseCount('facilities', 1);
    }

    public function test_koordinat_valid_menyimpan_waktu_pembaruan_koordinat(): void
    {
        $this->post(route('admin.facilities.store'), $this->payload([
            'latitude'  => -6.2,
            'longitude' => 106.8,
        ]))->assertSessionHasNoErrors();

        $facility = Facility::first();

        $this->assertNotNull($facility);
        $this->assertTrue($facility->hasCoordinates());
        $this->assertNotNull($facility->coordinate_updated_at);
        $this->assertNull(session('coord_warning'));
    }

    public function test_halaman_form_tambah_menampilkan_pemilih_lokasi(): void
    {
        $chain = $this->makeRegionChain();

        $this->get(route('admin.facilities.create'))
            ->assertOk()
            ->assertSee('Lokasi')
            ->assertSee('Wilayah Administratif')
            ->assertSee('Geser titik tanpa mouse')
            ->assertSee($chain['province']->name);
    }

    public function test_halaman_form_ubah_menampilkan_koordinat_tersimpan(): void
    {
        $chain    = $this->makeRegionChain();
        $facility = $this->makeFacility([
            'latitude'       => -6.2,
            'longitude'      => 106.8,
            'province_id'    => $chain['province']->region_id,
            'city_region_id' => $chain['city']->region_id,
            'district_id'    => $chain['district']->region_id,
            'region_id'      => $chain['village']->region_id,
        ]);

        $this->get(route('admin.facilities.edit', $facility->facility_id))
            ->assertOk()
            ->assertSee('Geser titik tanpa mouse')
            ->assertSee('-6.2', false);
    }
}
