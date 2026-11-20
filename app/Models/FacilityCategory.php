<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacilityCategory extends Model
{
    protected $table = 'facility_categories';

    protected $primaryKey = 'category_id';

    public $timestamps = false;

    protected $guarded = [];

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class, 'category_id', 'category_id');
    }
}
