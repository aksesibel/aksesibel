<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

/**
 * Endpoint GeoJSON peta: /api/fasilitas/peta
 */
class FacilityMapTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_hanya_fasilitas_berkoordinat_yang_dikembalikan(): void
    {
        $this->makeFacility(['name' => 'Ada Koordinat', 'latitude' => -6.2, 'longitude' => 106.8]);
        $this->makeFacility(['name' => 'Tanpa Koordinat', 'latitude' => null, 'longitude' => null]);

        $response = $this->getJson(route('api.facilities.map'));

        $response->assertOk()
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonPath('features.0.geometry.type', 'Point')
            ->assertJsonPath('features.0.properties.name', 'Ada Koordinat')
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.truncated', false);
    }

    public function test_filter_bounding_box_menyaring_fitur(): void
    {
        $this->makeFacility(['name' => 'Dalam BBox', 'latitude' => -6.2, 'longitude' => 106.8]);
        $this->makeFacility(['name' => 'Luar BBox',  'latitude' => -7.5, 'longitude' => 110.0]);

        $response = $this->getJson(route('api.facilities.map') . '?bbox=106.7,-6.3,106.9,-6.1');

        $response->assertOk()
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('features.0.properties.name', 'Dalam BBox');
    }

    public function test_bbox_dengan_format_salah_ditolak(): void
    {
        $this->getJson(route('api.facilities.map') . '?bbox=abc')
            ->assertStatus(422)
            ->assertJsonPath('error', 'Parameter bbox tidak valid. Format: minLng,minLat,maxLng,maxLat.');
    }

    public function test_bbox_dengan_nilai_min_lebih_besar_dari_maks_ditolak(): void
    {
        $this->getJson(route('api.facilities.map') . '?bbox=107,-6.3,106,-6.1')
            ->assertStatus(422);
    }

    public function test_filter_status_dan_pencarian(): void
    {
        $this->makeFacility(['name' => 'Terminal Uji', 'latitude' => -6.2, 'longitude' => 106.8, 'accessibility_status' => 'AKSES_PENUH']);
        $this->makeFacility(['name' => 'Stasiun Uji',  'latitude' => -6.3, 'longitude' => 106.9, 'accessibility_status' => 'BELUM_AKSESIBEL']);

        $this->getJson(route('api.facilities.map') . '?q=Terminal')
            ->assertOk()
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('features.0.properties.name', 'Terminal Uji');

        $this->getJson(route('api.facilities.map') . '?status=BELUM_AKSESIBEL')
            ->assertOk()
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('features.0.properties.status', 'BELUM_AKSESIBEL');
    }

    public function test_jumlah_fitur_dibatasi_dan_meta_truncated_diisi(): void
    {
        $this->assertSame(500, (int) config('aksesibel.map_max_features'));

        config()->set('aksesibel.map_max_features', 2);

        for ($i = 0; $i < 5; $i++) {
            $this->makeFacility([
                'name'      => "Fasilitas {$i}",
                'latitude'  => -6.2 + ($i * 0.001),
                'longitude' => 106.8,
            ]);
        }

        $response = $this->getJson(route('api.facilities.map'));

        $response->assertOk()
            ->assertJsonCount(2, 'features')
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.returned', 2)
            ->assertJsonPath('meta.truncated', true);
    }
}
