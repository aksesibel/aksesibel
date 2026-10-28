<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccessibilityType extends Model
{
    protected $table = 'accessibility_types';

    protected $primaryKey = 'accessibility_type_id';

    public $timestamps = false;

    protected $guarded = [];

    public function facilityAccessibilities(): HasMany
    {
        return $this->hasMany(FacilityAccessibility::class, 'accessibility_type_id', 'accessibility_type_id');
    }
}
