<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FollowUpProgress extends Model
{
    protected $table = 'follow_up_progress';

    protected $primaryKey = 'progress_id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(FollowUpAssignment::class, 'assignment_id', 'assignment_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(FollowUpEvidence::class, 'progress_id', 'progress_id');
    }
}
