<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Models\Facility;
use App\Models\FacilityCategory;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    // ---------------------------------------------------------------
    // Halaman Publik
    // ---------------------------------------------------------------

    /**
     * Beranda: tampilkan 3 fasilitas terbaru.
     */
    public function home()
    {
        $latestFacilities = Facility::with('category')
            ->latest()
            ->take(3)
            ->get();

        $totalFacilities = Facility::count();

        $activeCategories = FacilityCategory::where('is_active', true)
            ->withCount('facilities')
            ->orderBy('sort_order')
            ->get();

        return view('home', compact('latestFacilities', 'totalFacilities', 'activeCategories'));
    }

    /**
     * Daftar fasilitas publik dengan pencarian, filter, dan pagination.
     */
    public function index(Request $request)
    {
        $query = Facility::with('category');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('accessibility_status')) {
            $query->where('accessibility_status', $status);
        }

        $facilities = $query->latest()->paginate(9)->withQueryString();
        $categories = FacilityCategory::where('is_active', true)->withCount('facilities')->orderBy('sort_order')->orderBy('category_name')->get();

        return view('facilities.index', compact('facilities', 'categories'));
    }

    /**
     * Detail fasilitas publik.
     */
    public function show(Facility $facility)
    {
        $facility->load('category');
        return view('facilities.show', compact('facility'));
    }

    // ---------------------------------------------------------------
    // Panel Admin
    // ---------------------------------------------------------------

    /**
     * Tabel fasilitas di panel admin.
     */
    public function adminIndex(Request $request)
    {
        $query = Facility::with('category');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('accessibility_status')) {
            $query->where('accessibility_status', $status);
        }

        $facilities = $query->latest()->paginate(10)->withQueryString();

        return view('admin.facilities.index', compact('facilities'));
    }

    /**
     * Form tambah fasilitas.
     */
    public function create()
    {
        $categories    = FacilityCategory::where('is_active', true)->orderBy('sort_order')->orderBy('category_name')->get();
        $statusOptions = Facility::statusOptions();
        return view('admin.facilities.form', compact('categories', 'statusOptions'));
    }

    /**
     * Simpan fasilitas baru.
     */
    public function store(StoreFacilityRequest $request)
    {
        $data = $request->validated();

        if (isset($data['operating_hours'])) {
            $data['operating_hours'] = json_encode($data['operating_hours']);
        }

        Facility::create($data);

        return redirect()
            ->route('admin.facilities.index')
            ->with('toast_success', 'Fasilitas berhasil ditambahkan.');
    }

    /**
     * Form ubah fasilitas.
     */
    public function edit(Facility $facility)
    {
        // Sertakan kategori nonaktif yang sedang dipakai fasilitas ini agar tidak hilang dari pilihan
        $categories    = FacilityCategory::where('is_active', true)
            ->orWhere('category_id', $facility->category_id)
            ->orderBy('sort_order')
            ->orderBy('category_name')
            ->get();
        $statusOptions = Facility::statusOptions();
        return view('admin.facilities.form', compact('facility', 'categories', 'statusOptions'));
    }

    /**
     * Simpan perubahan fasilitas.
     */
    public function update(UpdateFacilityRequest $request, Facility $facility)
    {
        $data = $request->validated();

        if (isset($data['operating_hours'])) {
            $data['operating_hours'] = json_encode($data['operating_hours']);
        }

        $facility->update($data);

        return redirect()
            ->route('admin.facilities.index')
            ->with('toast_success', 'Fasilitas berhasil diperbarui.');
    }

    /**
     * Hapus fasilitas.
     */
    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()
            ->route('admin.facilities.index')
            ->with('toast_success', 'Fasilitas berhasil dihapus.');
    }
}
