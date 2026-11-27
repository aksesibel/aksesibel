<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Models\AccessibilityType;
use App\Models\Facility;
use App\Models\FacilityAccessibility;
use App\Models\FacilityCategory;
use App\Models\Region;
use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FacilityController extends Controller
{
    // ---------------------------------------------------------------
    // Halaman Publik
    // ---------------------------------------------------------------

    public function home(): View
    {
        $latestFacilities = $this->publicFacilities()
            ->latest()->take(3)->get();

        $totalFacilities = Facility::count();
        $totalAccessibilityTypes = AccessibilityType::count();
        $completedReports = Report::whereHas('status', fn (Builder $status) => $status->where('status_name', 'Selesai'))->count();

        $activeCategories = FacilityCategory::where('is_active', true)
            ->withCount('facilities')
            ->orderBy('sort_order')->get();

        return view('home', compact('latestFacilities', 'totalFacilities', 'totalAccessibilityTypes', 'completedReports', 'activeCategories'));
    }

    public function index(Request $request): View
    {
        $perPage = (int) config('aksesibel.facilities_per_page', 9);

        $query = $this->publicFacilities();
        $facilityFilters = $this->accessibilityFilters($request, $query);

        // Pencarian teks — termasuk nama wilayah (provinsi/kota/kecamatan/kelurahan)
        if ($search = $request->input('q')) {
            $regionIds = Region::where('name', 'like', "%{$search}%")->pluck('region_id');

            $query->where(function ($q) use ($search, $regionIds) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('postal_code', 'like', "%{$search}%")
                    ->orWhereIn('province_id', $regionIds)
                    ->orWhereIn('city_region_id', $regionIds)
                    ->orWhereIn('district_id', $regionIds)
                    ->orWhereIn('region_id', $regionIds);
            });
        }

        // Filter wilayah berjenjang
        if ($provinceId = $request->input('province_id')) {
            $query->where('province_id', $provinceId);
        }
        if ($cityId = $request->input('city_id')) {
            $query->where('city_region_id', $cityId);
        }
        if ($districtId = $request->input('district_id')) {
            $query->where('district_id', $districtId);
        }
        if ($regionId = $request->input('region_id')) {
            $query->where('region_id', $regionId);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('accessibility_status')) {
            $query->where('accessibility_status', $status);
        }

        // Koordinat pengguna untuk pengurutan jarak (tidak disimpan di server)
        $userLat = is_numeric($request->input('lat')) ? (float) $request->input('lat') : null;
        $userLng = is_numeric($request->input('lng')) ? (float) $request->input('lng') : null;
        $sortByDistance = $request->input('sort') === 'jarak' && $userLat !== null && $userLng !== null;

        if ($sortByDistance) {
            $all = $query->get()->each(function (Facility $f) use ($userLat, $userLng) {
                $f->distance_km = $f->distanceTo($userLat, $userLng);
            })->sortBy(fn (Facility $f) => $f->distance_km ?? PHP_FLOAT_MAX)->values();

            $page = max(1, (int) $request->input('page', 1));
            $facilities = new LengthAwarePaginator(
                $all->forPage($page, $perPage)->values(),
                $all->count(),
                $perPage,
                $page,
                ['path' => route('facilities.index'), 'query' => $request->query()]
            );
        } else {
            $facilities = $query->latest()->paginate($perPage)->withQueryString();
        }

        $categories = FacilityCategory::where('is_active', true)
            ->withCount('facilities')->orderBy('sort_order')->get();

        // Provinsi, kota, kecamatan untuk filter wilayah publik
        $provinces = Region::where('level', Region::LEVEL_PROVINSI)->orderBy('name')->get();
        $cities = $request->input('province_id')
            ? Region::where('level', Region::LEVEL_KOTA_KABUPATEN)->where('parent_id', $request->input('province_id'))->orderBy('name')->get()
            : collect();
        $districts = $request->input('city_id')
            ? Region::where('level', Region::LEVEL_KECAMATAN)->where('parent_id', $request->input('city_id'))->orderBy('name')->get()
            : collect();

        $accessibilityTypes = AccessibilityType::orderBy('type_name')->get(['accessibility_type_id', 'type_name']);

        return view('facilities.index', compact(
            'accessibilityTypes',
            'facilities', 'categories', 'provinces', 'cities', 'districts', 'sortByDistance', 'facilityFilters',
        ));
    }

    public function show(Facility $facility): View
    {
        $facility->load([
            'category', 'kelurahan', 'kecamatan', 'kotaWilayah', 'provinsi',
            'facilityAccessibilities' => fn ($items) => $items->with('accessibilityType')
                ->withMax('inspections', 'inspection_date')
                ->orderBy('facility_accessibility_id'),
        ]);
        $activeReports = $facility->reports()->whereHas(
            'status',
            fn (Builder $status) => $status->whereNotIn('status_name', ['Selesai', 'Ditolak', 'Dibatalkan']),
        )->count();

        return view('facilities.show', compact('facility', 'activeReports'));
    }

    private function publicFacilities(): Builder
    {
        return Facility::with(['category', 'kotaWilayah', 'kecamatan', 'kelurahan'])
            ->withCount([
                'facilityAccessibilities',
                'facilityAccessibilities as functional_accessibilities_count' => fn (Builder $items) => $items
                    ->where('availability_status', FacilityAccessibility::AVAILABILITY_AVAILABLE)
                    ->where('condition_status', FacilityAccessibility::CONDITION_GOOD),
            ]);
    }

    /** @return array{sarana?: list<int>, kondisi?: list<string>, pemeriksaan?: string|null} */
    private function accessibilityFilters(Request $request, Builder $facilities): array
    {
        $filters = $request->validate([
            'sarana' => ['sometimes', 'array', 'max:30'],
            'sarana.*' => ['integer', 'min:1'],
            'kondisi' => ['sometimes', 'array', 'max:3'],
            'kondisi.*' => [Rule::in(['good', 'repair', 'unavailable'])],
            'pemeriksaan' => ['nullable', Rule::in(['recorded', 'unrecorded'])],
        ]);

        $types = $filters['sarana'] ?? [];
        $conditions = $filters['kondisi'] ?? [];

        if ($types !== [] || $conditions !== []) {
            $facilities->whereHas('facilityAccessibilities', function (Builder $items) use ($types, $conditions): void {
                if ($types !== []) {
                    $items->whereIn('accessibility_type_id', $types);
                }

                if ($conditions !== []) {
                    $items->where(function (Builder $matching) use ($conditions): void {
                        if (in_array('good', $conditions, true)) {
                            $matching->orWhere(fn (Builder $item) => $item
                                ->where('availability_status', FacilityAccessibility::AVAILABILITY_AVAILABLE)
                                ->where('condition_status', FacilityAccessibility::CONDITION_GOOD));
                        }

                        if (in_array('repair', $conditions, true)) {
                            $matching->orWhere(fn (Builder $item) => $item
                                ->where('availability_status', FacilityAccessibility::AVAILABILITY_AVAILABLE)
                                ->whereIn('condition_status', [FacilityAccessibility::CONDITION_MINOR_DAMAGE, FacilityAccessibility::CONDITION_MAJOR_DAMAGE]));
                        }

                        if (in_array('unavailable', $conditions, true)) {
                            $matching->orWhere('availability_status', FacilityAccessibility::AVAILABILITY_UNAVAILABLE);
                        }
                    });
                }
            });
        }

        match ($filters['pemeriksaan'] ?? null) {
            'recorded' => $facilities->whereHas('facilityAccessibilities.inspections'),
            'unrecorded' => $facilities->whereDoesntHave('facilityAccessibilities.inspections'),
            default => null,
        };

        return $filters;
    }

    /**
     * GET /api/fasilitas/peta — GeoJSON untuk tampilan peta.
     *
     * Query: bbox=minLng,minLat,maxLng,maxLat; q; category/category_id;
     *        status/accessibility_status; province_id; city_id; district_id; region_id
     * Hanya fasilitas yang punya koordinat. Maksimal 500 fitur per permintaan.
     */
    public function mapData(Request $request)
    {
        $maxFeatures = (int) config('aksesibel.map_max_features', 500);

        $validator = Validator::make($request->query(), [
            'bbox' => ['nullable', 'string', 'regex:/^-?\d+(\.\d+)?(,-?\d+(\.\d+)?){3}$/'],
        ], [
            'bbox.regex' => 'Parameter bbox tidak valid. Format: minLng,minLat,maxLng,maxLat.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $query = Facility::with('category')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Bounding box
        if ($bbox = $request->input('bbox')) {
            [$minLng, $minLat, $maxLng, $maxLat] = array_map('floatval', explode(',', $bbox));

            if ($minLat > $maxLat || $minLng > $maxLng) {
                return response()->json([
                    'error' => 'Parameter bbox tidak valid: nilai minimum harus lebih kecil dari nilai maksimum.',
                ], 422);
            }

            $query->whereBetween('latitude', [$minLat, $maxLat])
                ->whereBetween('longitude', [$minLng, $maxLng]);
        }

        if ($search = $request->input('q')) {
            $regionIds = Region::where('name', 'like', "%{$search}%")->pluck('region_id');

            $query->where(function ($q) use ($search, $regionIds) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereIn('region_id', $regionIds)
                    ->orWhereIn('district_id', $regionIds)
                    ->orWhereIn('city_region_id', $regionIds)
                    ->orWhereIn('province_id', $regionIds);
            });
        }

        // Kategori — terima category_id (angka) maupun category (slug)
        if ($catId = $request->input('category_id')) {
            $query->where('category_id', $catId);
        } elseif ($catSlug = $request->input('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $catSlug));
        }

        // Status — terima status maupun accessibility_status
        $status = $request->input('status') ?? $request->input('accessibility_status');
        if ($status) {
            $query->where('accessibility_status', $status);
        }

        // Filter wilayah
        if ($value = $request->input('province_id')) {
            $query->where('province_id', $value);
        }
        if ($value = $request->input('city_id')) {
            $query->where('city_region_id', $value);
        }
        if ($value = $request->input('district_id')) {
            $query->where('district_id', $value);
        }
        if ($value = $request->input('region_id')) {
            $query->where('region_id', $value);
        }

        $this->accessibilityFilters($request, $query);

        $total = (clone $query)->count();
        $truncated = $total > $maxFeatures;
        $facilities = $query->limit($maxFeatures)->get();

        $features = $facilities->map(fn ($f) => [
            'type' => 'Feature',
            'geometry' => ['type' => 'Point', 'coordinates' => [(float) $f->longitude, (float) $f->latitude]],
            'properties' => [
                'id' => $f->facility_id,
                'name' => $f->name,
                'categoryName' => $f->category?->category_name,
                'categorySlug' => $f->category?->slug,
                'status' => $f->accessibility_status,
                'address' => $f->address,
                'city' => $f->kotaWilayah?->name ?? $f->city,
                'district' => $f->kecamatan?->name,
                'lat' => (float) $f->latitude,
                'lng' => (float) $f->longitude,
                'url' => route('facilities.show', $f->facility_id),
            ],
        ]);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
            'meta' => ['total' => $total, 'returned' => $facilities->count(), 'truncated' => $truncated],
        ]);
    }

    // ---------------------------------------------------------------
    // Panel Admin
    // ---------------------------------------------------------------

    public function adminIndex(Request $request)
    {
        $query = Facility::with(['category', 'kotaWilayah', 'kecamatan', 'kelurahan']);

        if ($search = $request->input('q')) {
            $regionIds = Region::where('name', 'like', "%{$search}%")->pluck('region_id');

            $query->where(function ($q) use ($search, $regionIds) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereIn('region_id', $regionIds)
                    ->orWhereIn('city_region_id', $regionIds)
                    ->orWhereIn('district_id', $regionIds);
            });
        }

        if ($status = $request->input('accessibility_status')) {
            $query->where('accessibility_status', $status);
        }

        if ($request->boolean('no_coords')) {
            $query->where(function ($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            });
        }

        $facilities = $query->latest()->paginate(10)->withQueryString();

        return view('admin.facilities.index', compact('facilities'));
    }

    public function create()
    {
        $categories = FacilityCategory::where('is_active', true)->orderBy('sort_order')->orderBy('category_name')->get();
        $statusOptions = Facility::statusOptions();
        $provinces = Region::where('level', Region::LEVEL_PROVINSI)->orderBy('name')->get();

        return view('admin.facilities.form', compact('categories', 'statusOptions', 'provinces'));
    }

    public function store(StoreFacilityRequest $request)
    {
        $data = $request->validated();

        if (isset($data['operating_hours'])) {
            $data['operating_hours'] = json_encode($data['operating_hours']);
        }

        if (! empty($data['latitude']) && ! empty($data['longitude'])) {
            $data['coordinate_updated_at'] = now();
            $data['coordinate_source'] = $data['coordinate_source'] ?? 'MANUAL';
        }

        Facility::create($data);

        return redirect()->route('admin.facilities.index')
            ->with('toast_success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Facility $facility)
    {
        $categories = FacilityCategory::where('is_active', true)
            ->orWhere('category_id', $facility->category_id)
            ->orderBy('sort_order')->orderBy('category_name')->get();
        $statusOptions = Facility::statusOptions();
        $provinces = Region::where('level', Region::LEVEL_PROVINSI)->orderBy('name')->get();
        $cities = $facility->province_id
            ? Region::where('level', Region::LEVEL_KOTA_KABUPATEN)->where('parent_id', $facility->province_id)->orderBy('name')->get()
            : collect();
        $districts = $facility->city_region_id
            ? Region::where('level', Region::LEVEL_KECAMATAN)->where('parent_id', $facility->city_region_id)->orderBy('name')->get()
            : collect();
        $villages = $facility->district_id
            ? Region::where('level', Region::LEVEL_KELURAHAN)->where('parent_id', $facility->district_id)->orderBy('name')->get()
            : collect();

        return view('admin.facilities.form', compact(
            'facility', 'categories', 'statusOptions',
            'provinces', 'cities', 'districts', 'villages'
        ));
    }

    public function update(UpdateFacilityRequest $request, Facility $facility)
    {
        $data = $request->validated();

        if (isset($data['operating_hours'])) {
            $data['operating_hours'] = json_encode($data['operating_hours']);
        }

        if (! empty($data['latitude']) && ! empty($data['longitude'])) {
            $data['coordinate_updated_at'] = now();
        }

        $facility->update($data);

        return redirect()->route('admin.facilities.index')
            ->with('toast_success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()->route('admin.facilities.index')
            ->with('toast_success', 'Fasilitas berhasil dihapus.');
    }
}
