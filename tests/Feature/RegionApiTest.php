<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

/**
 * Endpoint wilayah berjenjang: /api/wilayah dan /api/wilayah/{id}
 */
class RegionApiTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_daftar_wilayah_difilter_berdasarkan_level(): void
    {
        $chain = $this->makeRegionChain();

        $response = $this->getJson(route('api.regions.index', ['level' => 'PROVINSI']));

        $response->assertOk();

        $ids = collect($response->json())->pluck('region_id')->all();
        $this->assertContains($chain['province']->region_id, $ids);
        $this->assertNotContains($chain['city']->region_id, $ids);
    }

    public function test_daftar_wilayah_difilter_berdasarkan_induk(): void
    {
        $chain = $this->makeRegionChain();

        $response = $this->getJson(route('api.regions.index', [
            'level'     => 'KECAMATAN',
            'parent_id' => $chain['city']->region_id,
        ]));

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Kecamatan Uji')
            ->assertJsonPath('0.level', 'KECAMATAN');
    }

    public function test_daftar_wilayah_tanpa_filter_mengembalikan_semua_level(): void
    {
        $this->makeRegionChain();

        $response = $this->getJson(route('api.regions.index'));

        $response->assertOk()->assertJsonCount(4);
    }

    public function test_detail_wilayah_menyertakan_rantai_induk(): void
    {
        $chain = $this->makeRegionChain();

        $response = $this->getJson(route('api.regions.show', $chain['village']->region_id));

        $response->assertOk()
            ->assertJsonPath('region.name', 'Kelurahan Uji')
            ->assertJsonPath('region.level', 'KELURAHAN')
            ->assertJsonPath('chain.0.level', 'PROVINSI')
            ->assertJsonPath('chain.1.level', 'KOTA_KABUPATEN')
            ->assertJsonPath('chain.2.level', 'KECAMATAN')
            ->assertJsonPath('chain.3.level', 'KELURAHAN')
            ->assertJsonCount(4, 'chain');
    }

    public function test_detail_wilayah_tidak_ditemukan_mengembalikan_404(): void
    {
        $this->getJson(route('api.regions.show', 999999))->assertNotFound();
    }
}
