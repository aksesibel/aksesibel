<?php

namespace Tests\Feature;

use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

/**
 * Fasilitas lama tanpa koordinat (hanya kolom `city` lama) tetap valid dan tampil.
 */
class LegacyFacilityTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    private function makeLegacyFacility(): Facility
    {
        return Facility::create([
            'category_id'          => $this->makeCategory()->category_id,
            'name'                 => 'Fasilitas Lama Tanpa Koordinat',
            'description'          => 'Data lama sebelum fitur lokasi ditambahkan.',
            'address'              => 'Jl. Lama No. 1',
            'city'                 => 'Jakarta Pusat', // kolom lama, dipertahankan
            'accessibility_status' => Facility::STATUS_MENUNGGU_KONFIRMASI,
        ]);
    }

    public function test_fasilitas_lama_tanpa_koordinat_dianggap_tidak_punya_koordinat(): void
    {
        $facility = $this->makeLegacyFacility();

        $this->assertFalse($facility->fresh()->hasCoordinates());
        $this->assertNull($facility->fresh()->latitude);
        $this->assertNull($facility->fresh()->longitude);
    }

    public function test_fasilitas_lama_tetap_tampil_di_daftar_publik(): void
    {
        $this->makeLegacyFacility();

        $this->get(route('facilities.index'))
            ->assertOk()
            ->assertSee('Fasilitas Lama Tanpa Koordinat')
            ->assertSee('Lokasi belum ditentukan');
    }

    public function test_halaman_detail_menampilkan_lokasi_belum_ditentukan(): void
    {
        $facility = $this->makeLegacyFacility();

        $this->get(route('facilities.show', $facility->facility_id))
            ->assertOk()
            ->assertSee('Lokasi belum ditentukan')
            ->assertDontSee('mini-map');
    }

    public function test_fasilitas_lama_tidak_muncul_di_peta(): void
    {
        $this->makeLegacyFacility();

        $this->getJson(route('api.facilities.map'))
            ->assertOk()
            ->assertJsonCount(0, 'features');
    }
}
