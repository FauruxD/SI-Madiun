<?php

namespace App\Models;

use App\Enums\EProposalStatus;
use Database\Factories\ReviewEProposalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['eproposal_id', 'reviewer_id', 'status_sebelum', 'status_sesudah', 'aksi', 'catatan', 'reviewed_at'])]
class ReviewEProposal extends Model
{
    /** @use HasFactory<ReviewEProposalFactory> */
    use HasFactory;

    protected $table = 'review_eproposal';

    /** @return BelongsTo<EProposal, $this> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(EProposal::class, 'eproposal_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status_sebelum' => EProposalStatus::class,
            'status_sesudah' => EProposalStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }
}
