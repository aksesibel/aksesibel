<?php

namespace App\Providers;

use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
            return Region::where('region_id', $value)->firstOrFail();
        });

        Gate::define('manage-public-facilities', fn (User $user): bool => $user->account_status === 'active'
            && $user->role?->role_name === Role::ADMIN);
        Gate::define('manage-supporting-data', fn (User $user): bool => $user->canManageSupportingData());

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('registration', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
