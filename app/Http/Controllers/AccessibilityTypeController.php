<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccessibilityTypeController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $types = AccessibilityType::withCount('facilityAccessibilities')
            ->when($q !== '', fn ($query) => $query->where('type_name', 'like', "%{$q}%"))
            ->orderBy('type_name')
            ->get();

        return view('accessibility.types.index', compact('types', 'q'));
    }

    public function create()
    {
        return view('accessibility.types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type_name'   => ['required', 'string', 'max:255', 'unique:accessibility_types,type_name'],
            'description' => ['nullable', 'string'],
        ]);
        AccessibilityType::create($data);

        return redirect()->route('accessibility-types.index')
            ->with('success', 'Jenis aksesibilitas berhasil ditambahkan.');
    }

    public function edit(AccessibilityType $accessibilityType)
    {
        return view('accessibility.types.edit', compact('accessibilityType'));
    }

    public function update(Request $request, AccessibilityType $accessibilityType)
    {
        $data = $request->validate([
            'type_name'   => ['required', 'string', 'max:255',
                Rule::unique('accessibility_types', 'type_name')
                    ->ignore($accessibilityType->accessibility_type_id, 'accessibility_type_id')],
            'description' => ['nullable', 'string'],
        ]);
        $accessibilityType->update($data);

        return redirect()->route('accessibility-types.index')
            ->with('success', 'Jenis aksesibilitas berhasil diubah.');
    }

    public function destroy(AccessibilityType $accessibilityType)
    {
        if ($accessibilityType->facilityAccessibilities()->exists()) {
            return back()->withErrors(['hapus' => 'Jenis ini masih dipakai pada sarana, jadi tidak bisa dihapus.']);
        }
        $accessibilityType->delete();

        return redirect()->route('accessibility-types.index')
            ->with('success', 'Jenis aksesibilitas berhasil dihapus.');
    }
}