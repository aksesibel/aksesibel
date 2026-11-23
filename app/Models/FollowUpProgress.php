<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FollowUpProgress extends Model
{
    /**
     * Daftar status progres penanganan yang valid di sistem.
     *
     * @var array<int, string>
     */
    public const STATUS_OPTIONS = [
        'Sedang Dikerjakan',
        'Dalam Koordinasi Lapangan',
        'Menunggu Material/Peralatan',
        'Selesai',
    ];

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
