<?php

namespace Tests\Feature;

use App\Models\ReportingPeriod;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReportingPeriodResolutionTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @return array<string, array{string}> */
    public static function datesWithinPeriod(): array
    {
        return [
            'awal periode' => ['2026-10-01T00:00:00+07:00'],
            'tengah periode' => ['2026-10-15T12:00:00+07:00'],
            'akhir periode' => ['2026-10-31T23:59:59+07:00'],
            'UTC sudah masuk Oktober di Jakarta' => ['2026-09-30T17:00:00+00:00'],
            'UTC masih Oktober di Jakarta' => ['2026-10-31T16:59:59+00:00'],
        ];
    }

    #[DataProvider('datesWithinPeriod')]
    public function test_report_date_resolves_to_the_period_in_the_application_timezone(string $timestamp): void
    {
        $period = $this->createPeriod();
        $this->createPeriod(['period_name' => 'November', 'start_date' => '2026-11-01', 'end_date' => '2026-11-30']);

        $resolved = ReportingPeriod::forReportDate(new DateTimeImmutable($timestamp));

        $this->assertTrue($resolved->is($period));
    }

    /** @return array<string, array{string}> */
    public static function datesOutsidePeriod(): array
    {
        return [
            'sebelum periode' => ['2026-09-30T23:59:59+07:00'],
            'sesudah periode' => ['2026-11-01T00:00:00+07:00'],
        ];
    }

    #[DataProvider('datesOutsidePeriod')]
    public function test_report_date_without_a_matching_period_requires_manager_configuration(string $timestamp): void
    {
        $this->createPeriod();

        try {
            ReportingPeriod::forReportDate(new DateTimeImmutable($timestamp));
            $this->fail('Tanggal tanpa periode harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertSame([
                'period_id' => ['Belum ada periode pelaporan untuk tanggal laporan dibuat. Hubungi Pengelola Fasilitas.'],
            ], $exception->errors());
        }

        $this->assertDatabaseCount('reporting_periods', 1);
    }

    public function test_report_date_cannot_be_resolved_when_no_period_has_been_configured(): void
    {
        $this->expectException(ValidationException::class);

        ReportingPeriod::forReportDate(new DateTimeImmutable('2026-10-15T12:00:00+07:00'));
    }

    public function test_legacy_overlaps_are_reported_instead_of_choosing_an_arbitrary_period(): void
    {
        $this->createPeriod();
        $this->createPeriod(['period_name' => 'Triwulan IV', 'end_date' => '2026-12-31']);

        try {
            ReportingPeriod::forReportDate(new DateTimeImmutable('2026-10-15T12:00:00+07:00'));
            $this->fail('Tanggal dengan beberapa periode harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertSame([
                'period_id' => ['Tanggal laporan cocok dengan beberapa periode pelaporan. Hubungi Pengelola Fasilitas untuk memperbaiki rentang periode.'],
            ], $exception->errors());
        }

        $this->assertDatabaseCount('reporting_periods', 2);
    }

    /** @param array{period_name?: string, start_date?: string, end_date?: string} $overrides */
    private function createPeriod(array $overrides = []): ReportingPeriod
    {
        return ReportingPeriod::create(array_replace([
            'period_name' => 'Oktober',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ], $overrides));
    }
}
