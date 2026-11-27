<?php

namespace Tests\Feature;

use App\Models\AccessibilityType;
use App\Models\FacilityAccessibility;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class FacilityAccessibilityEditTest extends TestCase
{
    use CreatesTestData, LazilyRefreshDatabase;

    public function test_facility_manager_can_edit_existing_accessibility_details(): void
    {
        $role = Role::create(['role_name' => Role::FACILITY_MANAGER]);
        $manager = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
        $facility = $this->makeFacility();
        $type = AccessibilityType::create(['type_name' => 'Ramp']);
        $accessibility = FacilityAccessibility::create([
            'facility_id' => $facility->getKey(),
            'accessibility_type_id' => $type->getKey(),
            'location_description' => 'Pintu masuk utama',
            'photo_path' => 'facility-accessibilities/ramp.jpg',
        ]);

        $this->actingAs($manager)->get(route('facility-accessibilities.edit', $accessibility))
            ->assertOk()
            ->assertSee(route('facility-accessibilities.update', $accessibility))
            ->assertSee('Simpan Perubahan')
            ->assertSee('Pintu masuk utama');

        $this->put(route('facility-accessibilities.update', $accessibility), [
            'facility_id' => $facility->getKey(),
            'accessibility_type_id' => $type->getKey(),
            'location_description' => 'Pintu masuk timur',
        ])->assertRedirectToRoute('facility-accessibilities.index');

        $this->assertDatabaseHas('facility_accessibilities', [
            'facility_accessibility_id' => $accessibility->getKey(),
            'location_description' => 'Pintu masuk timur',
            'photo_path' => 'facility-accessibilities/ramp.jpg',
        ]);
    }
}
