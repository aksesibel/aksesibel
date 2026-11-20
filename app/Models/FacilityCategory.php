<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FacilityCategory extends Model
{
    protected $table = 'facility_categories';

    protected $primaryKey = 'category_id';

    public $timestamps = false;

    protected $fillable = [
        'category_name',
        'slug',
        'description',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $category): void {
            // Skema dapat diuji tanpa kolom slug (mis. fixture CategorySchemaTest).
            if (! $category->getConnection()->getSchemaBuilder()->hasColumn($category->getTable(), 'slug')) {
                return;
            }

            if (blank($category->slug)) {
                $category->slug = self::generateSlug(
                    (string) $category->category_name,
                    $category->category_id
                );
            }
        });
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Daftar ikon yang diizinkan (nama SVG inline — bukan lucide-react, karena ini Laravel)
    const ALLOWED_ICONS = [
        'Train'     => 'Kereta / Transportasi',
        'Bus'       => 'Bus / Terminal',
        'Trees'     => 'Taman / RTH',
        'Hospital'  => 'Rumah Sakit',
        'Landmark'  => 'Gedung Pemerintah',
        'Building2' => 'Gedung Umum',
        'School'    => 'Sekolah / Pendidikan',
        'Library'   => 'Perpustakaan',
        'MapPin'    => 'Lokasi Umum',
        'Warehouse' => 'Fasilitas Lainnya',
    ];

    public static function iconOptions(): array
    {
        return self::ALLOWED_ICONS;
    }

    /**
     * Generate slug unik dari nama kategori.
     */
    public static function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name, '-');
        $original = $slug;
        $count = 1;

        while (true) {
            $q = static::where('slug', $slug);
            if ($excludeId) {
                $q->where('category_id', '!=', $excludeId);
            }
            if (!$q->exists()) break;
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class, 'category_id', 'category_id');
    }

    public function activeFacilities(): HasMany
    {
        return $this->hasMany(Facility::class, 'category_id', 'category_id')
                    ->where('facility_status', 'active');
    }
}
