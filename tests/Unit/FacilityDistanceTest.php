<?php

namespace Tests\Unit;

use App\Models\Facility;
use PHPUnit\Framework\TestCase;

/**
 * Pengujian rumus Haversine (Facility::distanceTo) dan pengurutan berdasarkan jarak.
 */
class FacilityDistanceTest extends TestCase
{
    public function test_fasilitas_tanpa_koordinat_tidak_memiliki_jarak(): void
    {
        $facility = new Facility(['latitude' => null, 'longitude' => null]);

        $this->assertFalse($facility->hasCoordinates());
        $this->assertNull($facility->distanceTo(-6.2, 106.8));
    }

    public function test_jarak_ke_titik_yang_sama_adalah_nol(): void
    {
        $facility = new Facility(['latitude' => -6.2, 'longitude' => 106.8]);

        $this->assertTrue($facility->hasCoordinates());
        $this->assertSame(0.0, $facility->distanceTo(-6.2, 106.8));
    }

    public function test_haversine_monas_bandung_sesuai_perkiraan(): void
    {
        // Monas (-6.175392, 106.827153) → Bandung (-6.917464, 107.619123) ≈ 118 km
        $facility = new Facility(['latitude' => -6.175392, 'longitude' => 106.827153]);

        $distance = $facility->distanceTo(-6.917464, 107.619123);

        $this->assertNotNull($distance);
        $this->assertEqualsWithDelta(118.0, $distance, 5.0);
    }

    public function test_pengurutan_berdasarkan_jarak_menempatkan_tanpa_koordinat_di_akhir(): void
    {
        $userLat = -6.2;
        $userLng = 106.8;

        $dekat  = new Facility(['name' => 'Dekat',  'latitude' => -6.201, 'longitude' => 106.801]);
        $sedang = new Facility(['name' => 'Sedang', 'latitude' => -6.25,  'longitude' => 106.85]);
        $jauh   = new Facility(['name' => 'Jauh',   'latitude' => -6.9,   'longitude' => 107.6]);
        $tanpa  = new Facility(['name' => 'Tanpa',  'latitude' => null,   'longitude' => null]);

        $sorted = collect([$jauh, $tanpa, $dekat, $sedang])
            ->each(function (Facility $f) use ($userLat, $userLng) {
                $f->distance_km = $f->distanceTo($userLat, $userLng);
            })
            ->sortBy(fn (Facility $f) => $f->distance_km ?? PHP_FLOAT_MAX)
            ->values();

        $this->assertSame(['Dekat', 'Sedang', 'Jauh', 'Tanpa'], $sorted->pluck('name')->all());
        $this->assertNull($sorted->last()->distance_km);
    }
}
