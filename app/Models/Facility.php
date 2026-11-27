<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Facility extends Model
{
    protected $table      = 'facilities';
    protected $primaryKey = 'facility_id';
    public    $timestamps = true;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'address',
        'city',
        // Wilayah berjenjang
        'region_id',
        'province_id',
        'city_region_id',
        'district_id',
        // Koordinat
        'latitude',
        'longitude',
        'coordinate_source',
        'coordinate_updated_at',
        // Info tambahan
        'postal_code',
        'location_note',
        'entrance_note',
        'nearby_transit',
        // Kontak & status
        'operating_hours',
        'contact',
        'phone',
        'email',
        'facility_status',
        'accessibility_status',
    ];

    protected $casts = [
        'latitude'               => 'float',
        'longitude'              => 'float',
        'coordinate_updated_at'  => 'datetime',
    ];

    // ---------------------------------------------------------------
    // Status Aksesibilitas
    // ---------------------------------------------------------------
    const STATUS_AKSES_PENUH         = 'AKSES_PENUH';
    const STATUS_AKSES_SEBAGIAN      = 'AKSES_SEBAGIAN';
    const STATUS_BELUM_AKSESIBEL     = 'BELUM_AKSESIBEL';
    const STATUS_MENUNGGU_KONFIRMASI = 'MENUNGGU_KONFIRMASI';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_AKSES_PENUH         => 'Akses Penuh',
            self::STATUS_AKSES_SEBAGIAN      => 'Akses Sebagian',
            self::STATUS_BELUM_AKSESIBEL     => 'Belum Aksesibel',
            self::STATUS_MENUNGGU_KONFIRMASI => 'Menunggu Konfirmasi',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->accessibility_status] ?? $this->accessibility_status;
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Hitung jarak Haversine ke titik (lat, lng) dalam km.
     */
    public function distanceTo(float $lat, float $lng): ?float
    {
        if (!$this->hasCoordinates()) return null;

        $R    = 6371; // radius bumi km
        $dLat = deg2rad($lat - $this->latitude);
        $dLng = deg2rad($lng - $this->longitude);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($this->latitude)) * cos(deg2rad($lat)) * sin($dLng / 2) ** 2;

        return round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }

    /**
     * Alamat lengkap dengan wilayah.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->kelurahan?->name,
            $this->kecamatan?->name,
            $this->kotaWilayah?->name,
            $this->provinsi?->name,
            $this->postal_code,
        ]);

        return implode(', ', $parts) ?: ($this->address ?? '');
    }

    // ---------------------------------------------------------------
    // Relasi
    // ---------------------------------------------------------------
    public function category(): BelongsTo
    {
        return $this->belongsTo(FacilityCategory::class, 'category_id', 'category_id');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'district_id', 'region_id');
    }

    public function kotaWilayah(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'city_region_id', 'region_id');
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'province_id', 'region_id');
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
