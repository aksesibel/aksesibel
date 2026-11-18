<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacilityAccessibility extends Model
{
    protected $table = 'facility_accessibilities';

    protected $primaryKey = 'facility_accessibility_id';

    public $timestamps = false;

    protected $guarded = [];

    public const AVAILABILITY_AVAILABLE = 'available';
    public const AVAILABILITY_UNAVAILABLE = 'unavailable';

    public const CONDITION_GOOD = 'good';
    public const CONDITION_MINOR_DAMAGE = 'minor_damage';
    public const CONDITION_MAJOR_DAMAGE = 'major_damage';

    // di kode tetap bahasa Inggris, di interface akan menjadi bahasa Indonesia)
    public const AVAILABILITY_LABELS = [
        self::AVAILABILITY_AVAILABLE => 'Tersedia',
        self::AVAILABILITY_UNAVAILABLE => 'Tidak tersedia',
    ];

    public const CONDITION_LABELS = [
        self::CONDITION_GOOD => 'Baik',
        self::CONDITION_MINOR_DAMAGE => 'Rusak ringan',
        self::CONDITION_MAJOR_DAMAGE => 'Rusak berat',
    ];

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
