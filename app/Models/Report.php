<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Report extends Model
{
    protected $table = 'reports';

    protected $primaryKey = 'report_id';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'facility_id');
    }

    public function issueCategory(): BelongsTo
    {
        return $this->belongsTo(IssueCategory::class, 'issue_category_id', 'issue_category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(VerificationStatus::class, 'status_id', 'status_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(ReportingPeriod::class, 'period_id', 'period_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(ReportEvidence::class, 'report_id', 'report_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class, 'report_id', 'report_id');
    }

    public function followUpAssignments(): HasMany
    {
        return $this->hasMany(FollowUpAssignment::class, 'report_id', 'report_id');
    }
}
