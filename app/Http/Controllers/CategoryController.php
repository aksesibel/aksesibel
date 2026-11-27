<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Facility;
use App\Models\FacilityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    // ---------------------------------------------------------------
    // Halaman Publik
    // ---------------------------------------------------------------

    /**
     * Halaman per-kategori: daftar fasilitas dalam satu kategori.
     */
    public function show(string $slug)
    {
        $category = FacilityCategory::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $query = Facility::with('category')
            ->where('category_id', $category->category_id);

        if ($search = request('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($status = request('accessibility_status')) {
            $query->where('accessibility_status', $status);
        }

        $facilities = $query->latest()->paginate(9)->withQueryString();

        return view('categories.show', compact('category', 'facilities'));
    }

    // ---------------------------------------------------------------
    // Panel Admin
    // ---------------------------------------------------------------

    /**
     * Tabel kategori di panel admin.
     */
    public function adminIndex()
    {
        $categories = FacilityCategory::withCount('facilities')
            ->orderBy('sort_order')
            ->orderBy('category_name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Form tambah kategori.
     */
    public function create()
    {
        $iconOptions  = FacilityCategory::iconOptions();
        $nextOrder    = (FacilityCategory::max('sort_order') ?? 0) + 1;
        return view('admin.categories.form', compact('iconOptions', 'nextOrder'));
    }

    /**
     * Simpan kategori baru.
     */
    public function store(StoreCategoryRequest $request)
    {
        FacilityCategory::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('toast_success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Form ubah kategori.
     */
    public function edit(FacilityCategory $category)
    {
        $iconOptions = FacilityCategory::iconOptions();
        return view('admin.categories.form', compact('category', 'iconOptions'));
    }

    /**
     * Simpan perubahan kategori.
     */
    public function update(UpdateCategoryRequest $request, FacilityCategory $category)
    {
        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('toast_success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Toggle aktif/nonaktif kategori.
     */
    public function toggleActive(FacilityCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        $label = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.categories.index')
            ->with('toast_success', "Kategori \"{$category->category_name}\" berhasil {$label}.");
    }

    /**
     * Hapus kategori — dengan opsi pindah fasilitas.
     */
    public function destroy(Request $request, FacilityCategory $category)
    {
        $facilityCount = $category->facilities()->count();
        $moveTo        = $request->input('move_to');

        if ($facilityCount > 0) {
            if (!$moveTo) {
                // Kembalikan ke halaman dengan pesan error agar dialog tampil
                return redirect()
                    ->route('admin.categories.index')
                    ->with('toast_error', "Kategori \"{$category->category_name}\" masih memiliki {$facilityCount} fasilitas. Pindahkan fasilitas terlebih dahulu atau pilih kategori tujuan.");
            }

            $targetCategory = FacilityCategory::find($moveTo);
            if (!$targetCategory) {
                return redirect()
                    ->route('admin.categories.index')
                    ->with('toast_error', 'Kategori tujuan tidak ditemukan.');
            }

            // Pindah semua fasilitas, lalu hapus kategori — atomik dalam transaksi
            DB::transaction(function () use ($category, $targetCategory) {
                Facility::where('category_id', $category->category_id)
                    ->update(['category_id' => $targetCategory->category_id]);
                $category->delete();
            });

            return redirect()
                ->route('admin.categories.index')
                ->with('toast_success', "Kategori dihapus. {$facilityCount} fasilitas dipindahkan ke \"{$targetCategory->category_name}\".");
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('toast_success', "Kategori \"{$category->category_name}\" berhasil dihapus.");
    }

    /**
     * Simpan ulang urutan (sort_order) semua kategori.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:facility_categories,category_id'],
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->input('order') as $sortOrder => $categoryId) {
                FacilityCategory::where('category_id', $categoryId)
                    ->update(['sort_order' => $sortOrder + 1]);
            }
        });

        return redirect()
            ->route('admin.categories.index')
            ->with('toast_success', 'Urutan kategori berhasil disimpan.');
    }
}
