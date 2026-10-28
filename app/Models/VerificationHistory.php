<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VerificationHistory extends Model
{
    protected $table = 'verification_histories';

    protected $primaryKey = 'history_id';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    public function verification(): BelongsTo
    {
        return $this->belongsTo(Verification::class, 'verification_id', 'verification_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }

    public function previousStatus(): BelongsTo
    {
        return $this->belongsTo(VerificationStatus::class, 'previous_status_id', 'status_id');
    }

    public function newStatus(): BelongsTo
    {
        return $this->belongsTo(VerificationStatus::class, 'new_status_id', 'status_id');
    }
}
