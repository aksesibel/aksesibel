<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacilityCategoryRequest;
use App\Models\FacilityCategory;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FacilityCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
        $categories = FacilityCategory::query()
            ->when(filled($filters['q'] ?? null), fn ($query) => $query->where('category_name', 'like', '%'.$filters['q'].'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('is_active', $status === 'active'))
            ->withCount('facilities')
            ->orderBy('category_name')->orderBy('category_id')
            ->paginate(15)->withQueryString();

        return view('manage.categories.index', [
            'categories' => $categories,
            'title' => 'Kategori Fasilitas',
            'routePrefix' => 'manage.facility-categories',
            'usageLabel' => 'fasilitas',
            'usageAttribute' => 'facilities_count',
        ]);
    }

    public function create(): View
    {
        return view('manage.categories.form', [
            'category' => new FacilityCategory,
            'title' => 'Kategori Fasilitas',
            'routePrefix' => 'manage.facility-categories',
        ]);
    }

    public function store(FacilityCategoryRequest $request): RedirectResponse
    {
        try {
            $category = FacilityCategory::create($request->validated());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['category_name' => 'Nama kategori sudah digunakan.']);
        }

        return to_route('manage.facility-categories.show', $category)->with('success', 'Kategori fasilitas berhasil ditambahkan.');
    }

    public function show(FacilityCategory $facilityCategory): View
    {
        $facilityCategory->loadCount('facilities');

        return view('manage.categories.show', [
            'category' => $facilityCategory,
            'title' => 'Kategori Fasilitas',
            'routePrefix' => 'manage.facility-categories',
            'usageCount' => $facilityCategory->facilities_count,
            'usageLabel' => 'fasilitas',
        ]);
    }

    public function edit(FacilityCategory $facilityCategory): View
    {
        return view('manage.categories.form', [
            'category' => $facilityCategory,
            'title' => 'Kategori Fasilitas',
            'routePrefix' => 'manage.facility-categories',
        ]);
    }

    public function update(FacilityCategoryRequest $request, FacilityCategory $facilityCategory): RedirectResponse
    {
        try {
            $facilityCategory->update($request->validated());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['category_name' => 'Nama kategori sudah digunakan.']);
        }

        return to_route('manage.facility-categories.show', $facilityCategory)->with('success', 'Kategori fasilitas berhasil diperbarui.');
    }

    public function destroy(FacilityCategory $facilityCategory): RedirectResponse
    {
        if ($facilityCategory->facilities()->exists()) {
            return back()->with('error', 'Kategori sedang digunakan fasilitas. Nonaktifkan kategori agar histori tetap tersedia.');
        }

        try {
            $facilityCategory->delete();
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451
                && ! str_contains($exception->getMessage(), 'FOREIGN KEY constraint failed')) {
                throw $exception;
            }

            return back()->with('error', 'Kategori sedang digunakan fasilitas. Nonaktifkan kategori agar histori tetap tersedia.');
        }

        return to_route('manage.facility-categories.index')->with('success', 'Kategori fasilitas berhasil dihapus.');
    }
}
