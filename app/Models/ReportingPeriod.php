<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class ReportingPeriod extends Model
{
    protected $table = 'reporting_periods';

    protected $primaryKey = 'period_id';

    public $timestamps = false;

    protected $guarded = [];

    public static function forReportDate(DateTimeInterface $createdAt): self
    {
        $date = CarbonImmutable::instance($createdAt)->setTimezone(config('app.timezone'))->toDateString();
        $periods = static::query()
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->limit(2)
            ->get();

        if ($periods->isEmpty()) {
            throw ValidationException::withMessages([
                'period_id' => 'Belum ada periode pelaporan untuk tanggal laporan dibuat. Hubungi Pengelola Fasilitas.',
            ]);
        }

        if ($periods->count() > 1) {
            throw ValidationException::withMessages([
                'period_id' => 'Tanggal laporan cocok dengan beberapa periode pelaporan. Hubungi Pengelola Fasilitas untuk memperbaiki rentang periode.',
            ]);
        }

        return $periods->first();
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'period_id', 'period_id');
    }
}
