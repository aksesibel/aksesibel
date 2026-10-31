<?php

namespace App\Http\Controllers;

use App\Http\Requests\IssueCategoryRequest;
use App\Models\IssueCategory;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IssueCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
        $categories = IssueCategory::query()
            ->when(filled($filters['q'] ?? null), fn ($query) => $query->where('category_name', 'like', '%'.$filters['q'].'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('is_active', $status === 'active'))
            ->withCount('reports')
            ->orderBy('category_name')->orderBy('issue_category_id')
            ->paginate(15)->withQueryString();

        return view('manage.categories.index', [
            'categories' => $categories,
            'title' => 'Kategori Kendala',
            'routePrefix' => 'manage.issue-categories',
            'usageLabel' => 'laporan',
            'usageAttribute' => 'reports_count',
        ]);
    }

    public function create(): View
    {
        return view('manage.categories.form', [
            'category' => new IssueCategory,
            'title' => 'Kategori Kendala',
            'routePrefix' => 'manage.issue-categories',
        ]);
    }

    public function store(IssueCategoryRequest $request): RedirectResponse
    {
        try {
            $category = IssueCategory::create($request->validated());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['category_name' => 'Nama kategori sudah digunakan.']);
        }

        return to_route('manage.issue-categories.show', $category)->with('success', 'Kategori kendala berhasil ditambahkan.');
    }

    public function show(IssueCategory $issueCategory): View
    {
        $issueCategory->loadCount('reports');

        return view('manage.categories.show', [
            'category' => $issueCategory,
            'title' => 'Kategori Kendala',
            'routePrefix' => 'manage.issue-categories',
            'usageCount' => $issueCategory->reports_count,
            'usageLabel' => 'laporan',
        ]);
    }

    public function edit(IssueCategory $issueCategory): View
    {
        return view('manage.categories.form', [
            'category' => $issueCategory,
            'title' => 'Kategori Kendala',
            'routePrefix' => 'manage.issue-categories',
        ]);
    }

    public function update(IssueCategoryRequest $request, IssueCategory $issueCategory): RedirectResponse
    {
        try {
            $issueCategory->update($request->validated());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['category_name' => 'Nama kategori sudah digunakan.']);
        }

        return to_route('manage.issue-categories.show', $issueCategory)->with('success', 'Kategori kendala berhasil diperbarui.');
    }

    public function destroy(IssueCategory $issueCategory): RedirectResponse
    {
        if ($issueCategory->reports()->exists()) {
            return back()->with('error', 'Kategori sedang digunakan laporan. Nonaktifkan kategori agar histori tetap tersedia.');
        }

        try {
            $issueCategory->delete();
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451
                && ! str_contains($exception->getMessage(), 'FOREIGN KEY constraint failed')) {
                throw $exception;
            }

            return back()->with('error', 'Kategori sedang digunakan laporan. Nonaktifkan kategori agar histori tetap tersedia.');
        }

        return to_route('manage.issue-categories.index')->with('success', 'Kategori kendala berhasil dihapus.');
    }
}
