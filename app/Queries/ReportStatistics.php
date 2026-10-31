<?php

namespace App\Queries;

use App\Models\Facility;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportStatistics
{
    /** @return array<string, string> */
    public function missingDependencies(): array
    {
        $dependencies = [
            'facilities' => 'Data fasilitas dari modul fasilitas belum tersedia.',
            'reports' => 'Data laporan dari modul pelaporan belum tersedia.',
            'facility_categories' => 'Master kategori fasilitas belum tersedia.',
            'issue_categories' => 'Master kategori kendala belum tersedia.',
            'verification_statuses' => 'Master status verifikasi dari modul verifikasi belum tersedia.',
            'reporting_periods' => 'Master periode pelaporan belum tersedia.',
        ];

        return array_filter($dependencies, fn (string $table): bool => ! Schema::hasTable($table), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param  array{period_id?: int|string|null, category_id?: int|string|null, issue_category_id?: int|string|null, page?: int|string|null}  $filters
     * @return array{facilityTotal: int, reportTotal: int, byStatus: Collection, byIssue: Collection, byFacility: Collection, recap: LengthAwarePaginator}
     */
    public function summarize(array $filters): array
    {
        $reports = $this->filteredReports($filters);

        return [
            'facilityTotal' => Facility::query()->count(),
            'reportTotal' => (clone $reports)->count('reports.report_id'),
            'byStatus' => (clone $reports)
                ->select('verification_statuses.status_id', 'verification_statuses.status_name as label')
                ->selectRaw('COUNT(reports.report_id) as total')
                ->groupBy('verification_statuses.status_id', 'verification_statuses.status_name')
                ->orderBy('verification_statuses.status_name')->orderBy('verification_statuses.status_id')->get(),
            'byIssue' => (clone $reports)
                ->select('issue_categories.issue_category_id', 'issue_categories.category_name as label', 'issue_categories.is_active')
                ->selectRaw('COUNT(reports.report_id) as total')
                ->groupBy('issue_categories.issue_category_id', 'issue_categories.category_name', 'issue_categories.is_active')
                ->orderBy('issue_categories.category_name')->orderBy('issue_categories.issue_category_id')->get(),
            'byFacility' => (clone $reports)
                ->select('facility_categories.category_id', 'facility_categories.category_name as label', 'facility_categories.is_active')
                ->selectRaw('COUNT(reports.report_id) as total')
                ->groupBy('facility_categories.category_id', 'facility_categories.category_name', 'facility_categories.is_active')
                ->orderBy('facility_categories.category_name')->orderBy('facility_categories.category_id')->get(),
            'recap' => (clone $reports)
                ->select(
                    'facility_categories.category_id',
                    'facility_categories.category_name as facility_category',
                    'facility_categories.is_active as facility_category_active',
                    'issue_categories.issue_category_id',
                    'issue_categories.category_name as issue_category',
                    'issue_categories.is_active as issue_category_active',
                    'verification_statuses.status_id',
                    'verification_statuses.status_name',
                )
                ->selectRaw('COUNT(reports.report_id) as total')
                ->groupBy(
                    'facility_categories.category_id', 'facility_categories.category_name', 'facility_categories.is_active',
                    'issue_categories.issue_category_id', 'issue_categories.category_name', 'issue_categories.is_active',
                    'verification_statuses.status_id', 'verification_statuses.status_name',
                )
                ->orderBy('facility_categories.category_name')->orderBy('facility_categories.category_id')
                ->orderBy('issue_categories.category_name')->orderBy('issue_categories.issue_category_id')
                ->orderBy('verification_statuses.status_name')->orderBy('verification_statuses.status_id')
                ->paginate(15)->appends(array_filter($filters, fn (mixed $value, string $key): bool => $value !== null && $key !== 'page', ARRAY_FILTER_USE_BOTH)),
        ];
    }

    /** @param array{period_id?: int|string|null, category_id?: int|string|null, issue_category_id?: int|string|null, page?: int|string|null} $filters */
    private function filteredReports(array $filters): Builder
    {
        return DB::table('reports')
            ->join('facilities', 'facilities.facility_id', '=', 'reports.facility_id')
            ->join('facility_categories', 'facility_categories.category_id', '=', 'facilities.category_id')
            ->join('issue_categories', 'issue_categories.issue_category_id', '=', 'reports.issue_category_id')
            ->join('verification_statuses', 'verification_statuses.status_id', '=', 'reports.status_id')
            ->when($filters['period_id'] ?? null, fn (Builder $query, mixed $id): Builder => $query->where('reports.period_id', $id))
            ->when($filters['category_id'] ?? null, fn (Builder $query, mixed $id): Builder => $query->where('facilities.category_id', $id))
            ->when($filters['issue_category_id'] ?? null, fn (Builder $query, mixed $id): Builder => $query->where('reports.issue_category_id', $id));
    }
}
