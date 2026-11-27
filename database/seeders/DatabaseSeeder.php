<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(MasterDataSeeder::class);

        // Data wilayah — DKI Jakarta
        $this->call(RegionSeeder::class);

        // Data contoh fasilitas publik
        $this->call(FacilitySeeder::class);
    }
}
