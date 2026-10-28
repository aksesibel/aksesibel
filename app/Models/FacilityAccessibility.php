<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FacilityAccessibility extends Model
{
    protected $table = 'facility_accessibilities';

    protected $primaryKey = 'facility_accessibility_id';

    public $timestamps = false;

    protected $guarded = [];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'facility_id');
    }

    public function accessibilityType(): BelongsTo
    {
        return $this->belongsTo(AccessibilityType::class, 'accessibility_type_id', 'accessibility_type_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(AccessibilityInspection::class, 'facility_accessibility_id', 'facility_accessibility_id');
    }
}
