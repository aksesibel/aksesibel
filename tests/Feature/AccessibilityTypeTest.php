<?php

namespace Tests\Feature;

use App\Models\AccessibilityType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_dapat_membuka_halaman_daftar_jenis_aksesibilitas(): void
    {
        $response = $this->get(route('accessibility-types.index'));

        $response->assertOk();
        $response->assertViewIs('accessibility-types.index');
    }

    public function test_pengguna_dapat_mencari_jenis_aksesibilitas(): void
    {
        AccessibilityType::create([
            'type_name' => 'Ramp',
            'description' => 'Jalur bidang miring dengan kemiringan landai.',
        ]);

        AccessibilityType::create([
            'type_name' => 'Toilet Penyandang Disabilitas',
            'description' => 'Fasilitas sanitasi khusus untuk penyandang disabilitas.',
        ]);

        $response = $this->get(route('accessibility-types.index', [
            'q' => 'Ramp',
        ]));

        $response->assertOk();
        $response->assertSee('Ramp');
        $response->assertDontSee('Toilet Penyandang Disabilitas');
    }

    public function test_pengguna_dapat_membuka_halaman_tambah_jenis_aksesibilitas(): void
    {
        $response = $this->get(route('accessibility-types.create'));

        $response->assertOk();
        $response->assertViewIs('accessibility-types.create');
    }

    public function test_pengguna_dapat_menambahkan_jenis_aksesibilitas(): void
    {
        $response = $this->post(route('accessibility-types.store'), [
            'type_name' => 'Informasi Audio',
            'description' => 'Sistem pengumuman pengeras suara, layar interaktif bersuara, atau alarm berbunyi yang memberikan informasi nomor antrean, arah lokasi, maupun peringatan kondisi darurat bagi penyandang disabilitas netra.',
        ]);

        $response->assertRedirect(route('accessibility-types.index'));

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('accessibility_types', [
            'type_name' => 'Informasi Audio',
            'description' => 'Sistem pengumuman pengeras suara, layar interaktif bersuara, atau alarm berbunyi yang memberikan informasi nomor antrean, arah lokasi, maupun peringatan kondisi darurat bagi penyandang disabilitas netra.',
        ]);
    }

    public function test_pengguna_tidak_dapat_menambahkan_jenis_tanpa_nama(): void
    {
        $response = $this->post(route('accessibility-types.store'), [
            'type_name' => '',
            'description' => 'Deskripsi jenis aksesibilitas.',
        ]);

        $response->assertSessionHasErrors('type_name');

        $this->assertDatabaseCount('accessibility_types', 0);
    }

    public function test_pengguna_tidak_dapat_menambahkan_nama_jenis_yang_sudah_ada(): void
    {
        AccessibilityType::create([
            'type_name' => 'Ramp',
            'description' => 'Jalur landai.',
        ]);

        $response = $this->post(route('accessibility-types.store'), [
            'type_name' => 'Ramp',
            'description' => 'Deskripsi lainnya.',
        ]);

        $response->assertSessionHasErrors('type_name');

        $this->assertDatabaseCount('accessibility_types', 1);
    }

    public function test_pengguna_dapat_membuka_halaman_edit_jenis_aksesibilitas(): void
    {
        $type = AccessibilityType::create([
            'type_name' => 'Display Taktual / Braille',
            'description' => 'Peta dengan informasi yang dapat diraba.',
        ]);

        $response = $this->get(route(
            'accessibility-types.edit',
            $type->accessibility_type_id
        ));

        $response->assertOk();
        $response->assertViewIs('accessibility-types.edit');
        $response->assertViewHas('accessibilityType', $type);
    }

    public function test_pengguna_dapat_mengubah_jenis_aksesibilitas(): void
    {
        $type = AccessibilityType::create([
            'type_name' => 'Informasi Audio',
            'description' => 'Deskripsi lama.',
        ]);

        $response = $this->put(
            route('accessibility-types.update', $type->accessibility_type_id),
            [
                'type_name' => 'Informasi Audio',
                'description' => 'Deskripsi yang sudah diperbarui.',
            ]
        );

        $response->assertRedirect(route('accessibility-types.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('accessibility_types', [
            'accessibility_type_id' => $type->accessibility_type_id,
            'type_name' => 'Informasi Audio',
            'description' => 'Deskripsi yang sudah diperbarui.',
        ]);
    }

    public function test_pengguna_dapat_menghapus_jenis_aksesibilitas_yang_tidak_digunakan(): void
    {
        $type = AccessibilityType::create([
            'type_name' => 'Peta Taktil',
            'description' => 'Peta dengan informasi yang dapat diraba.',
        ]);

        $response = $this->delete(
            route('accessibility-types.destroy', $type->accessibility_type_id)
        );

        $response->assertRedirect(route('accessibility-types.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('accessibility_types', [
            'accessibility_type_id' => $type->accessibility_type_id,
        ]);
    }
}
