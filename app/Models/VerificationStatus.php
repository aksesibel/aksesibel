<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VerificationStatus extends Model
{
    protected $table = 'verification_statuses';

    protected $primaryKey = 'status_id';

    public $timestamps = false;

    protected $guarded = [];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'status_id', 'status_id');
    }
}
