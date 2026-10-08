<?php

namespace App\Models;

use App\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'phone', 'role', 'is_active', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
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
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /** @return BelongsToMany<Desa, $this> */
    public function desas(): BelongsToMany
    {
        return $this->belongsToMany(Desa::class, 'user_desa')->withTimestamps();
    }

    /** @return BelongsToMany<KawasanPerdesaan, $this> */
    public function kawasans(): BelongsToMany
    {
        return $this->belongsToMany(KawasanPerdesaan::class, 'user_kawasan', 'user_id', 'kawasan_id')
            ->withTimestamps();
    }

    /** @return HasMany<ActivityLog, $this> */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /** @return HasMany<EProposal, $this> */
    public function eproposals(): HasMany
    {
        return $this->hasMany(EProposal::class, 'submitted_by');
    }

    /** @return HasMany<ReviewEProposal, $this> */
    public function reviewedEproposals(): HasMany
    {
        return $this->hasMany(ReviewEProposal::class, 'reviewer_id');
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }
}
