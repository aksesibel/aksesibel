<?php

namespace App\Http\Controllers;

use App\Models\IssueCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IssueCategoryController extends Controller
{
    /**
     * Menampilkan seluruh kategori kendala.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', '');

        // Validasi pilihan filter status.
        if (!in_array($status, ['active', 'inactive', ''], true)) {
            $status = '';
        }

        $totalCategories = IssueCategory::count();

        $activeCategories = IssueCategory::where(
            'is_active',
            true
        )->count();

        $inactiveCategories = IssueCategory::where(
            'is_active',
            false
        )->count();

        $categories = IssueCategory::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(
                    'category_name',
                    'like',
                    "%{$search}%"
                );
            })
            ->when($status === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($status === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->orderBy('category_name')
            ->paginate(10)
            ->appends([
                'search' => $search,
                'status' => $status,
            ]);

        return view('issue-categories.index', compact(
            'categories',
            'search',
            'status',
            'totalCategories',
            'activeCategories',
            'inactiveCategories'
        ));
    }

    /**
     * Menampilkan form tambah kategori.
     */
    public function create()
    {
        return view('issue-categories.create');
    }

    /**
     * Menyimpan kategori baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => [
                'required',
                'string',
                'max:255',
                'unique:issue_categories,category_name',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['is_active'] = true;

        IssueCategory::create($validated);

        return redirect()
            ->route('issue-categories.index')
            ->with('success', 'Kategori kendala berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit kategori.
     */
    public function edit(IssueCategory $issueCategory)
    {
        return view('issue-categories.edit', compact('issueCategory'));
    }

    /**
     * Memperbarui kategori.
     */
    public function update(
        Request $request,
        IssueCategory $issueCategory
    ) {
        $validated = $request->validate([
            'category_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('issue_categories', 'category_name')
                    ->ignore(
                        $issueCategory->issue_category_id,
                        'issue_category_id'
                    ),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $issueCategory->update($validated);

        return redirect()
            ->route('issue-categories.index')
            ->with('success', 'Kategori kendala berhasil diperbarui.');
    }

    /**
     * Mengubah status aktif/nonaktif kategori.
     */
    public function toggleStatus(IssueCategory $issueCategory)
    {
        $issueCategory->update([
            'is_active' => !$issueCategory->is_active,
        ]);

        return redirect()
            ->route('issue-categories.index')
            ->with('success', 'Status kategori berhasil diperbarui.');
    }
}
