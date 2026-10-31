<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role_id', 'account_status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const STATUSES = ['active', 'inactive', 'suspended'];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function canManageSupportingData(): bool
    {
        return $this->account_status === 'active'
            && $this->role?->role_name === Role::FACILITY_MANAGER;
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'user_id', 'id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(AccessibilityInspection::class, 'inspector_id', 'id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class, 'verifier_id', 'id');
    }

    public function verificationHistories(): HasMany
    {
        return $this->hasMany(VerificationHistory::class, 'actor_id', 'id');
    }

    public function assignmentsReceived(): HasMany
    {
        return $this->hasMany(FollowUpAssignment::class, 'assigned_to', 'id');
    }

    public function assignmentsGiven(): HasMany
    {
        return $this->hasMany(FollowUpAssignment::class, 'assigned_by', 'id');
    }

    public function followUpProgress(): HasMany
    {
        return $this->hasMany(FollowUpProgress::class, 'actor_id', 'id');
    }
}
