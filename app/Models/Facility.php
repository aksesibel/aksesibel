<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Facility extends Model
{
    protected $table = 'facilities';

    protected $primaryKey = 'facility_id';

    public $timestamps = false;

    protected $guarded = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FacilityCategory::class, 'category_id', 'category_id');
    }

    public function location(): HasOne
    {
        return $this->hasOne(FacilityLocation::class, 'facility_id', 'facility_id');
    }

    public function facilityAccessibilities(): HasMany
    {
        return $this->hasMany(FacilityAccessibility::class, 'facility_id', 'facility_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'facility_id', 'facility_id');
    }
}
