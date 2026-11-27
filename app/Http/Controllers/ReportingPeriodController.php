<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportingPeriodRequest;
use App\Models\ReportingPeriod;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportingPeriodController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:255']]);
        $periods = ReportingPeriod::query()
            ->when(filled($filters['q'] ?? null), fn ($query) => $query->where('period_name', 'like', '%'.$filters['q'].'%'))
            ->withCount('reports')
            ->orderByDesc('start_date')->orderByDesc('period_id')
            ->paginate(15)->withQueryString();

        return view('manage.periods.index', compact('periods'));
    }

    public function create(): View
    {
        return view('manage.periods.form', ['period' => new ReportingPeriod]);
    }

    public function store(ReportingPeriodRequest $request): RedirectResponse
    {
        $period = ReportingPeriod::create($request->validated());

        return to_route('manage.reporting-periods.show', $period)->with('success', 'Periode pelaporan berhasil ditambahkan.');
    }

    public function show(ReportingPeriod $reportingPeriod): View
    {
        $reportingPeriod->loadCount('reports');

        return view('manage.periods.show', ['period' => $reportingPeriod]);
    }

    public function edit(ReportingPeriod $reportingPeriod): View
    {
        return view('manage.periods.form', ['period' => $reportingPeriod]);
    }

    public function update(ReportingPeriodRequest $request, ReportingPeriod $reportingPeriod): RedirectResponse
    {
        $reportingPeriod->update($request->validated());

        return to_route('manage.reporting-periods.show', $reportingPeriod)->with('success', 'Periode diperbarui. Laporan yang terhubung tetap pada periode ini.');
    }

    public function destroy(ReportingPeriod $reportingPeriod): RedirectResponse
    {
        if ($reportingPeriod->reports()->exists()) {
            return back()->with('error', 'Periode digunakan laporan dan tidak dapat dihapus. Hubungan laporan harus tetap dipertahankan.');
        }

        try {
            $reportingPeriod->delete();
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451
                && ! str_contains($exception->getMessage(), 'FOREIGN KEY constraint failed')) {
                throw $exception;
            }

            return back()->with('error', 'Periode digunakan laporan dan tidak dapat dihapus. Hubungan laporan harus tetap dipertahankan.');
        }

        return to_route('manage.reporting-periods.index')->with('success', 'Periode pelaporan berhasil dihapus.');
    }
}
