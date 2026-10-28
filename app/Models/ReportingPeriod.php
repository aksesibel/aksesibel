<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReportingPeriod extends Model
{
    protected $table = 'reporting_periods';

    protected $primaryKey = 'period_id';

    public $timestamps = false;

    protected $guarded = [];

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
