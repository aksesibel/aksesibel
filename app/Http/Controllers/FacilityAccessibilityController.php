<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityType;
use App\Models\Facility;
use App\Models\FacilityAccessibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

    public function create()
    {
        return view('facility-accessibilities.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')
                ->store('facility-accessibilities', 'public');
        }

        FacilityAccessibility::create($validated);

        return redirect()
            ->route('facility-accessibilities.index')
            ->with('success', 'Data aksesibilitas fasilitas berhasil ditambahkan.');
    }


    public function show(FacilityAccessibility $facilityAccessibility)
    {
        $facilityAccessibility->load([
            'facility',
            'accessibilityType',
        ]);

        $inspections = $facilityAccessibility->inspections()
            ->with('inspector')
            ->orderByDesc('inspection_date')
            ->orderByDesc('inspection_id')
            ->paginate(3)
            ->withQueryString();

        return view('facility-accessibilities.show', compact(
            'facilityAccessibility',
            'inspections'
        ));
    }

    public function edit(FacilityAccessibility $facilityAccessibility)
    {
        return view(
            'facility-accessibilities.edit',
            $this->formOptions() + compact('facilityAccessibility')
        );
    }

    public function update(Request $request, FacilityAccessibility $facilityAccessibility)
    {
        $validated = $this->validateData($request, $facilityAccessibility);

        $oldPhoto = $facilityAccessibility->photo_path;
        $newPhoto = null;

        if ($request->hasFile('photo')) {
            $newPhoto = $request->file('photo')->store('facility-accessibilities', 'public');
            $validated['photo_path'] = $newPhoto;
        }

        try {
            $facilityAccessibility->update($validated);
        } catch (\Throwable $e) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }
            throw $e;
        }

        if ($newPhoto && $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()
            ->route('facility-accessibilities.index')
            ->with('success', 'Data aksesibilitas fasilitas berhasil diperbarui.');
    }

    public function destroy(FacilityAccessibility $facilityAccessibility)
    {
        if ($facilityAccessibility->inspections()->exists()) {
            return redirect()
                ->route('facility-accessibilities.index')
                ->with('error', 'Data tidak dapat dihapus karena sudah memiliki data inspeksi.');
        }

        $photo = $facilityAccessibility->photo_path;

        $facilityAccessibility->delete();

        if ($photo) {
            Storage::disk('public')->delete($photo);
        }

        return redirect()
            ->route('facility-accessibilities.index')
            ->with('success', 'Data aksesibilitas fasilitas berhasil dihapus.');
    }

    /**
     * Data untuk dropdown di form create dan edit.
     */
    private function formOptions(): array
    {
        return [
            'facilities' => Facility::orderBy('name')->get(),
            'accessibilityTypes' => AccessibilityType::orderBy('type_name')->get(),
        ];
    }

    /**
     * Validasi input. Wajib karena model memakai $guarded = [].
     */
    private function validateData(Request $request, ?FacilityAccessibility $facilityAccessibility = null): array
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'exists:facilities,facility_id'],
            'accessibility_type_id' => [
                'required',
                'exists:accessibility_types,accessibility_type_id',
                // satu fasilitas tidak boleh punya tipe aksesibilitas yang sama dua kali
                Rule::unique('facility_accessibilities', 'accessibility_type_id')
                    ->where('facility_id', $request->input('facility_id'))
                    ->ignore($facilityAccessibility?->facility_accessibility_id, 'facility_accessibility_id'),
            ],
            'availability_status' => ['nullable', Rule::in(array_keys(FacilityAccessibility::AVAILABILITY_LABELS))],
            'condition_status' => ['nullable', Rule::in(array_keys(FacilityAccessibility::CONDITION_LABELS))],
            'location_description' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'accessibility_type_id.unique' => 'Fasilitas ini sudah memiliki tipe aksesibilitas tersebut.',
        ]);

        unset($validated['photo']); // 'photo' bukan kolom, yang disimpan 'photo_path'

        return $validated;
    }
}