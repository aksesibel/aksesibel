<?php

namespace App\Queries;

use App\Models\Facility;
use App\Models\FacilityAccessibility;
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
            'regions' => 'Data wilayah fasilitas belum tersedia.',
            'facility_accessibilities' => 'Data kondisi sarana fasilitas belum tersedia.',
            'verifications' => 'Data verifikasi laporan belum tersedia.',
            'verification_histories' => 'Riwayat verifikasi laporan belum tersedia.',
        ];

        return array_filter($dependencies, fn (string $table): bool => ! Schema::hasTable($table), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param  array{period_id?: int|string|null, category_id?: int|string|null, issue_category_id?: int|string|null, region_id?: int|string|null, page?: int|string|null}  $filters
     * @return array{facilityTotal: int, reportTotal: int, byStatus: Collection, byIssue: Collection, byFacility: Collection, byMonth: Collection, byRegion: Collection, byCondition: Collection, recap: LengthAwarePaginator}
     */
    public function summarize(array $filters): array
    {
        $reports = $this->filteredReports($filters);

        return [
            'facilityTotal' => Facility::query()->count(),
            'reportTotal' => (clone $reports)->count('reports.report_id'),
            'byMonth' => $this->monthlyTrend($reports),
            'byRegion' => (clone $reports)->leftJoin('regions', 'regions.region_id', '=', 'facilities.city_region_id')
                ->selectRaw("COALESCE(regions.name, NULLIF(facilities.city, ''), 'Wilayah belum tercatat') AS label, COUNT(reports.report_id) AS total")
                ->groupBy('label')->orderByDesc('total')->orderBy('label')->get(),
            'byCondition' => $this->conditionBreakdown($filters),
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

    private function monthlyTrend(Builder $reports): Collection
    {
        $received = (clone $reports)->whereNotNull('reports.created_at')
            ->selectRaw('SUBSTR(reports.created_at, 1, 7) AS month, COUNT(reports.report_id) AS total')
            ->groupBy('month')->pluck('total', 'month');

        $lastCompletion = DB::table('verification_histories')
            ->join('verification_statuses', 'verification_statuses.status_id', '=', 'verification_histories.new_status_id')
            ->join('verifications', 'verifications.verification_id', '=', 'verification_histories.verification_id')
            ->where('verification_statuses.status_name', 'Selesai')
            ->select('verifications.report_id')->selectRaw('MAX(verification_histories.changed_at) AS finished_at')
            ->groupBy('verifications.report_id');

        $completed = (clone $reports)->joinSub($lastCompletion, 'completion', 'completion.report_id', '=', 'reports.report_id')
            ->where('verification_statuses.status_name', 'Selesai')->whereNotNull('completion.finished_at')
            ->selectRaw('SUBSTR(completion.finished_at, 1, 7) AS month, COUNT(reports.report_id) AS total')
            ->groupBy('month')->pluck('total', 'month');

        return $received->keys()->merge($completed->keys())->unique()->sortDesc()->take(12)->sort()->values()
            ->map(fn (string $month): array => [
                'month' => $month,
                'received' => (int) $received->get($month, 0),
                'completed' => (int) $completed->get($month, 0),
            ]);
    }

    /** @param array{category_id?: int|string|null, region_id?: int|string|null} $filters */
    private function conditionBreakdown(array $filters): Collection
    {
        return DB::table('facility_accessibilities')
            ->join('facilities', 'facilities.facility_id', '=', 'facility_accessibilities.facility_id')
            ->when($filters['category_id'] ?? null, fn (Builder $query, mixed $id): Builder => $query->where('facilities.category_id', $id))
            ->when($filters['region_id'] ?? null, fn (Builder $query, mixed $id): Builder => $query->where('facilities.city_region_id', $id))
            ->selectRaw(
                "CASE
                    WHEN availability_status = ? THEN 'Belum tersedia'
                    WHEN availability_status = ? AND condition_status = ? THEN 'Berfungsi'
                    WHEN condition_status IN (?, ?) THEN 'Perlu perbaikan'
                    ELSE 'Belum diperiksa'
                END AS label, COUNT(facility_accessibility_id) AS total",
                [
                    FacilityAccessibility::AVAILABILITY_UNAVAILABLE,
                    FacilityAccessibility::AVAILABILITY_AVAILABLE,
                    FacilityAccessibility::CONDITION_GOOD,
                    FacilityAccessibility::CONDITION_MINOR_DAMAGE,
                    FacilityAccessibility::CONDITION_MAJOR_DAMAGE,
                ],
            )
            ->groupBy('label')->orderByDesc('total')->orderBy('label')->get();
    }

    /** @param array{period_id?: int|string|null, category_id?: int|string|null, issue_category_id?: int|string|null, region_id?: int|string|null, page?: int|string|null} $filters */
    private function filteredReports(array $filters): Builder
    {
        return DB::table('reports')
            ->join('facilities', 'facilities.facility_id', '=', 'reports.facility_id')
            ->join('facility_categories', 'facility_categories.category_id', '=', 'facilities.category_id')
            ->join('issue_categories', 'issue_categories.issue_category_id', '=', 'reports.issue_category_id')
            ->join('verification_statuses', 'verification_statuses.status_id', '=', 'reports.status_id')
            ->when($filters['period_id'] ?? null, fn (Builder $query, mixed $id): Builder => $query->where('reports.period_id', $id))
            ->when($filters['category_id'] ?? null, fn (Builder $query, mixed $id): Builder => $query->where('facilities.category_id', $id))
            ->when($filters['issue_category_id'] ?? null, fn (Builder $query, mixed $id): Builder => $query->where('reports.issue_category_id', $id))
            ->when($filters['region_id'] ?? null, fn (Builder $query, mixed $id): Builder => $query->where('facilities.city_region_id', $id));
    }
}
