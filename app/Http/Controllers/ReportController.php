<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityAccessibility;
use App\Models\IssueCategory;
use App\Models\Report;
use App\Models\ReportEvidence;
use App\Models\ReportingPeriod;
use App\Models\VerificationStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Menampilkan daftar laporan milik pengguna yang login.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()
                ->route('reports.create')
                ->with('info', 'Silakan login untuk melihat laporan Anda.');
        }

        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';
        $statusId = filter_var($request->query('status_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $sort = $request->query('sort') === 'oldest' ? 'oldest' : 'latest';
        $direction = $sort === 'oldest' ? 'asc' : 'desc';

        $statuses = VerificationStatus::orderBy('status_name')->get();

        $reports = Report::with([
            'facility',
            'issueCategory',
            'status',
        ])
            ->where('user_id', auth()->id())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('report_number', 'like', "%{$search}%")
                        ->orWhere('report_title', 'like', "%{$search}%")
                        ->orWhereHas('facility', function ($facilityQuery) use ($search) {
                            $facilityQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($statusId, fn ($query) => $query->where('status_id', $statusId))
            ->orderBy('created_at', $direction)
            ->orderBy('report_id', $direction)
            ->paginate(10)
            ->appends(['search' => $search, 'status_id' => $statusId, 'sort' => $sort]);

        return view('reports.index', compact(
            'reports',
            'search',
            'statuses',
            'statusId',
            'sort'
        ));
    }

    /**
     * Menampilkan form pembuatan laporan.
     */
    public function create(): View
    {
        $facilities = Facility::where('facility_status', 'active')
            ->orderBy('name')
            ->get();

        $categories = IssueCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        $activePeriod = null;
        $periodError = null;

        try {
            $activePeriod = ReportingPeriod::forReportDate(now());
        } catch (ValidationException $exception) {
            $periodError = $exception->errors()['period_id'][0];
        }

        return view('reports.create', compact(
            'facilities',
            'categories',
            'activePeriod',
            'periodError',
        ));
    }

    /**
     * Menyimpan laporan dan bukti pendukung.
     */
    public function store(Request $request): RedirectResponse
    {
        // Identitas pelapor tidak boleh berasal dari input form.
        if (! auth()->check()) {
            return back()->withErrors([
                'authentication' => 'Silakan login terlebih dahulu sebelum mengirim laporan.',
            ])->withInput();
        }

        $reportCreatedAt = now();
        $activePeriod = ReportingPeriod::forReportDate($reportCreatedAt);

        $status = VerificationStatus::where(
            'status_name',
            'Menunggu Verifikasi'
        )->first();

        if (! $status) {
            return back()->withErrors([
                'status' => 'Status awal laporan belum tersedia.',
            ])->withInput();
        }

        $validated = $request->validate([
            'facility_id' => [
                'required',
                'exists:facilities,facility_id',
            ],
            'issue_category_id' => [
                'required',
                'exists:issue_categories,issue_category_id',
            ],
            'report_title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'max:5000',
            ],
            'incident_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'specific_location' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_anonymous' => [
                'nullable',
                'boolean',
            ],
            'evidences' => [
                'nullable',
                'array',
                'max:5',
            ],
            'evidences.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'facility_accessibility_id' => [
                'nullable',
                'exists:facility_accessibilities,facility_accessibility_id',
            ],
        ]);

        if ($request->filled('facility_accessibility_id')) {
            $accessibilityExists = FacilityAccessibility::where(
                'facility_accessibility_id',
                $request->facility_accessibility_id
            )
                ->where('facility_id', $request->facility_id)
                ->exists();

            if (! $accessibilityExists) {
                return back()
                    ->withErrors([
                        'facility_accessibility_id' => 'Item aksesibilitas tidak sesuai dengan fasilitas yang dipilih.',
                    ])
                    ->withInput();
            }
        }

        // Pastikan fasilitas dan kategori masih aktif.
        $facility = Facility::where(
            'facility_id',
            $validated['facility_id']
        )->where('facility_status', 'active')->first();

        if (! $facility) {
            return back()->withErrors([
                'facility_id' => 'Fasilitas tidak tersedia atau sudah tidak aktif.',
            ])->withInput();
        }

        $category = IssueCategory::where(
            'issue_category_id',
            $validated['issue_category_id']
        )->where('is_active', true)->first();

        if (! $category) {
            return back()->withErrors([
                'issue_category_id' => 'Kategori kendala tidak tersedia atau sudah tidak aktif.',
            ])->withInput();
        }

        $storedFiles = [];

        try {
            $report = DB::transaction(function () use (
                $request,
                $validated,
                $activePeriod,
                $status,
                $reportCreatedAt,
                &$storedFiles
            ) {
                $report = Report::create([
                    'report_number' => 'LAP-'.$reportCreatedAt->format('Ymd').'-'.Str::upper(Str::random(8)),
                    'report_title' => $validated['report_title'],
                    'user_id' => auth()->id(),
                    'facility_id' => $validated['facility_id'],
                    'facility_accessibility_id' => $validated['facility_accessibility_id'] ?? null,
                    'issue_category_id' => $validated['issue_category_id'],
                    'status_id' => $status->status_id,
                    'period_id' => $activePeriod->period_id,
                    'created_at' => $reportCreatedAt,
                    'description' => $validated['description'],
                    'incident_date' => $validated['incident_date'],
                    'specific_location' => $validated['specific_location'] ?? null,
                    'is_anonymous' => $request->boolean('is_anonymous'),
                ]);

                foreach ($request->file('evidences', []) as $file) {
                    $path = $file->store('report-evidences', 'public');

                    if (! $path) {
                        throw new \RuntimeException('Gagal menyimpan file bukti.');
                    }

                    $storedFiles[] = $path;

                    ReportEvidence::create([
                        'report_id' => $report->report_id,
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'caption' => null,
                    ]);
                }

                return $report;
            });
        } catch (\Throwable $e) {
            if (count($storedFiles)) {
                Storage::disk('public')->delete($storedFiles);
            }

            report($e);

            return back()->withErrors([
                'submission' => 'Laporan gagal disimpan. Silakan coba kembali.',
            ])->withInput();
        }

        return redirect()
            ->route('reports.show', $report->report_id)
            ->with('success', 'Laporan berhasil dikirim.');
    }

    /**
     * Menampilkan detail laporan milik pengguna yang login.
     */
    public function show(Report $report): View
    {
        abort_unless(
            auth()->check() && $report->user_id === auth()->id(),
            403
        );

        $report->load([
            'facility',
            'facilityAccessibility.accessibilityType',
            'issueCategory',
            'status',
            'period',
            'evidences',
        ]);

        return view('reports.show', compact('report'));
    }

    public function getFacilityAccessibilities(Facility $facility): JsonResponse
    {
        abort_unless($facility->facility_status === 'active', 404);

        $accessibilities = $facility->facilityAccessibilities()->with('accessibilityType')->get();

        return response()->json(
            $accessibilities->map(function ($item) {
                return [
                    'id' => $item->facility_accessibility_id,
                    'name' => $item->accessibilityType->type_name ?? 'Item aksesibilitas',
                    'location' => $item->location_description,
                    'condition' => $item->condition_status,
                    'availability' => $item->availability_status,
                ];
            })
        );
    }
}
