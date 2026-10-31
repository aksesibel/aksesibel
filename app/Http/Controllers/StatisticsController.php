<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatisticsRequest;
use App\Models\FacilityCategory;
use App\Models\IssueCategory;
use App\Models\ReportingPeriod;
use App\Queries\ReportStatistics;
use Illuminate\Contracts\View\View;

class StatisticsController extends Controller
{
    public function index(StatisticsRequest $request, ReportStatistics $statistics): View
    {
        $missingDependencies = $statistics->missingDependencies();

        if ($missingDependencies !== []) {
            return view('statistics.index', compact('missingDependencies'));
        }

        return view('statistics.index', [
            'missingDependencies' => [],
            'filters' => $request->validated(),
            'periods' => ReportingPeriod::query()->orderByDesc('start_date')->orderByDesc('period_id')->get(),
            'facilityCategories' => FacilityCategory::query()->orderBy('category_name')->orderBy('category_id')->get(),
            'issueCategories' => IssueCategory::query()->orderBy('category_name')->orderBy('issue_category_id')->get(),
            ...$statistics->summarize($request->validated()),
        ]);
    }
}
