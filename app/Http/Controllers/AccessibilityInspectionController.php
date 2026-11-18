<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityInspection;
use App\Models\FacilityAccessibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccessibilityInspectionController extends Controller
{
    public function store(Request $request, FacilityAccessibility $facilityAccessibility)
    {
        $validated = $request->validate([
            'inspection_date' => ['required', 'date'],
            'condition_result' => [
                'required',
                Rule::in(array_keys(FacilityAccessibility::CONDITION_LABELS)),
            ],
            'notes' => ['nullable', 'string'],
            'information_source' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($facilityAccessibility, $validated) {

            // Menyimpan riwayat pemeriksaan
            AccessibilityInspection::create([
                'facility_accessibility_id' => $facilityAccessibility->facility_accessibility_id,
                'inspector_id' => 1, // Sementara untuk development lokal
                'inspection_date' => $validated['inspection_date'],
                'condition_result' => $validated['condition_result'],
                'notes' => $validated['notes'] ?? null,
                'information_source' => $validated['information_source'] ?? null,
            ]);

            // Memperbarui kondisi terkini sarana
            $facilityAccessibility->update([
                'condition_status' => $validated['condition_result'],
            ]);
        });

        return redirect()
            ->route('facility-accessibilities.show', $facilityAccessibility->facility_accessibility_id)
            ->with('success', 'Hasil pemeriksaan berhasil dicatat.');
    }
}
