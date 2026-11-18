<?php

namespace App\Http\Controllers;

use App\Models\FacilityAccessibility;
use Illuminate\Http\Request;

class FacilityAccessibilityController extends Controller
{
    public function index(Request $request)
    {
        $query = FacilityAccessibility::with([
            'facility',
            'accessibilityType'
        ]);

        // Fitur pencarian
        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($q) use ($search) {
                $q->whereHas('facility', function ($facilityQuery) use ($search) {
                    $facilityQuery->where('name', 'like', "%{$search}%");
                });

                $q->orWhereHas('accessibilityType', function ($typeQuery) use ($search) {
                    $typeQuery->where('type_name', 'like', "%{$search}%");
                });
            });
        }

        $facilityAccessibilities = $query
            ->orderByDesc('facility_accessibility_id')
            ->paginate(10)
            ->withQueryString();

        return view(
            'facility-accessibilities.index',
            compact('facilityAccessibilities')
        );
    }
}