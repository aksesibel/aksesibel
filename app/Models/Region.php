<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $table      = 'regions';
    protected $primaryKey = 'region_id';
    public    $timestamps = true;

    protected $fillable = ['code', 'name', 'level', 'parent_id'];

    // Level constants
    const LEVEL_PROVINSI        = 'PROVINSI';
    const LEVEL_KOTA_KABUPATEN  = 'KOTA_KABUPATEN';
    const LEVEL_KECAMATAN       = 'KECAMATAN';
    const LEVEL_KELURAHAN       = 'KELURAHAN';

    const LEVEL_LABELS = [
        self::LEVEL_PROVINSI       => 'Provinsi',
        self::LEVEL_KOTA_KABUPATEN => 'Kota/Kabupaten',
        self::LEVEL_KECAMATAN      => 'Kecamatan',
        self::LEVEL_KELURAHAN      => 'Kelurahan/Desa',
    ];

    // Sumber koordinat
    const COORD_MANUAL   = 'MANUAL';
    const COORD_PIN_PETA = 'PIN_PETA';
    const COORD_GEOCODE  = 'GEOKODING';
    const COORD_DEVICE   = 'LOKASI_PERANGKAT';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id', 'region_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Region::class, 'parent_id', 'region_id');
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class, 'region_id', 'region_id');
    }

    /**
     * Kembalikan rantai induk: Provinsi > Kota > Kecamatan > Kelurahan
     */
    public function getAncestorChainAttribute(): string
    {
        $parts = [];
        $current = $this;

        while ($current) {
            array_unshift($parts, $current->name);
            $current = $current->parent;
        }

        return implode(' › ', $parts);
    }

    /**
     * Label level dalam Bahasa Indonesia.
     */
    public function getLevelLabelAttribute(): string
    {
        return self::LEVEL_LABELS[$this->level] ?? $this->level;
    }
}
