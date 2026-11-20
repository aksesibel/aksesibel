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

    public $timestamps = true;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'address',
        'city',
        'operating_hours',
        'contact',
        'phone',
        'email',
        'facility_status',
        'accessibility_status',
    ];

    // Status aksesibilitas
    const STATUS_AKSES_PENUH        = 'AKSES_PENUH';
    const STATUS_AKSES_SEBAGIAN     = 'AKSES_SEBAGIAN';
    const STATUS_BELUM_AKSESIBEL    = 'BELUM_AKSESIBEL';
    const STATUS_MENUNGGU_KONFIRMASI = 'MENUNGGU_KONFIRMASI';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_AKSES_PENUH        => 'Akses Penuh',
            self::STATUS_AKSES_SEBAGIAN     => 'Akses Sebagian',
            self::STATUS_BELUM_AKSESIBEL    => 'Belum Aksesibel',
            self::STATUS_MENUNGGU_KONFIRMASI => 'Menunggu Konfirmasi',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->accessibility_status] ?? $this->accessibility_status;
    }

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
