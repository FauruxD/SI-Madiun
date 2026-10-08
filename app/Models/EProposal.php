<?php

namespace App\Models;

use App\Enums\EProposalStatus;
use App\UserRole;
use Database\Factories\EProposalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['potensi_desa_id', 'submitted_by', 'nomor_proposal', 'status', 'catatan_pengaju', 'submitted_at', 'verified_at', 'published_at'])]
class EProposal extends Model
{
    /** @use HasFactory<EProposalFactory> */
    use HasFactory;

    protected $table = 'eproposal';

    /** @return BelongsTo<PotensiDesa, $this> */
    public function potential(): BelongsTo
    {
        return $this->belongsTo(PotensiDesa::class, 'potensi_desa_id');
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /** @return HasMany<ReviewEProposal, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(ReviewEProposal::class, 'eproposal_id')->latest('reviewed_at')->latest('id');
    }

    /** @param Builder<EProposal> $query */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->role === UserRole::AdminDesa) {
            return $query->whereHas('potential', fn (Builder $potentials) => $potentials->whereIn(
                'desa_id',
                Desa::query()->visibleTo($user)->select('id'),
            ));
        }

        if ($user->role === UserRole::AdminKawasan) {
            return $query->whereHas('potential.village.kawasans', fn (Builder $kawasans) => $kawasans->visibleTo($user));
        }

        return $query->whereKey(0);
    }

    /** @param Builder<EProposal> $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', EProposalStatus::Published->value)
            ->whereHas('potential', fn (Builder $potentials) => $potentials->published());
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => EProposalStatus::class,
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }
}
