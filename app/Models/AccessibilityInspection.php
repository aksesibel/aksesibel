<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccessibilityInspection extends Model
{
    protected $table = 'accessibility_inspections';

    protected $primaryKey = 'inspection_id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'inspection_date' => 'date',
        ];
    }

    public function facilityAccessibility(): BelongsTo
    {
        return $this->belongsTo(FacilityAccessibility::class, 'facility_accessibility_id', 'facility_accessibility_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id', 'id');
    }
}
