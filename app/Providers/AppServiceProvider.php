<?php

namespace App\Providers;

use App\Models\Facility;
use App\Models\FacilityCategory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Facility — primary key facility_id
        Route::bind('facility', function ($value) {
            return Facility::where('facility_id', $value)->firstOrFail();
        });

        // FacilityCategory — primary key category_id
        Route::bind('category', function ($value) {
            return FacilityCategory::where('category_id', $value)->firstOrFail();
        });

        // Region — primary key region_id
        Route::bind('region', function ($value) {
            return \App\Models\Region::where('region_id', $value)->firstOrFail();
        });
    }
}
