<?php

namespace App\Services;

use App\Actions\RecordActivity;
use App\Enums\EProposalStatus;
use App\Enums\PotensiStatus;
use App\Models\EProposal;
use App\Models\PotensiDesa;
use App\Models\ReviewEProposal;
use App\Models\User;
use App\Notifications\EProposalStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EProposalWorkflowService
{
    public function __construct(private RecordActivity $recordActivity) {}

    public function submit(PotensiDesa $potential, User $actor, ?string $note = null, ?string $ipAddress = null): EProposal
    {
        return DB::transaction(function () use ($potential, $actor, $note, $ipAddress): EProposal {
            $lockedPotential = PotensiDesa::query()->lockForUpdate()->findOrFail($potential->getKey());

            if (EProposal::query()->where('potensi_desa_id', $lockedPotential->getKey())->exists()) {
                throw ValidationException::withMessages(['potensi_desa_id' => 'Potensi ini sudah memiliki E-Proposal.']);
            }

            if ($lockedPotential->status !== PotensiStatus::Draft) {
                throw ValidationException::withMessages(['potensi_desa_id' => 'Potensi yang sudah Published tidak dapat diajukan. Aturan proposal untuk publikasi lama belum ditetapkan.']);
            }

            $proposal = EProposal::query()->create([
                'potensi_desa_id' => $lockedPotential->getKey(),
                'submitted_by' => $actor->getKey(),
                'status' => EProposalStatus::Diajukan,
                'catatan_pengaju' => $note,
                'submitted_at' => now(),
            ]);

            $this->recordReview($proposal, $actor, null, EProposalStatus::Diajukan, 'submitted', $note);
            $this->recordActivity->execute('eproposal.submitted', $proposal, ['status' => EProposalStatus::Diajukan->value], $actor, $ipAddress);
            $this->notifySubmitter($proposal, EProposalStatus::Diajukan, 'Proposal berhasil diajukan.');

            return $proposal->load(['potential.village.kecamatan', 'potential.category', 'submitter', 'reviews.reviewer']);
        });
    }

    public function requestRevision(EProposal $proposal, User $reviewer, string $note, ?string $ipAddress = null): EProposal
    {
        return $this->transition($proposal, $reviewer, EProposalStatus::PerluRevisi, 'revision_requested', $note, $ipAddress);
    }

    public function resubmit(EProposal $proposal, User $actor, ?string $note = null, ?string $ipAddress = null): EProposal
    {
        return $this->transition($proposal, $actor, EProposalStatus::Diajukan, 'resubmitted', $note, $ipAddress);
    }

    public function verify(EProposal $proposal, User $reviewer, ?string $ipAddress = null): EProposal
    {
        return $this->transition($proposal, $reviewer, EProposalStatus::Terverifikasi, 'verified', null, $ipAddress);
    }

    public function publish(EProposal $proposal, User $reviewer, ?string $ipAddress = null): EProposal
    {
        return DB::transaction(function () use ($proposal, $reviewer, $ipAddress): EProposal {
            $lockedPotential = PotensiDesa::query()->lockForUpdate()->findOrFail($proposal->potensi_desa_id);
            $lockedProposal = EProposal::query()
                ->lockForUpdate()
                ->findOrFail($proposal->getKey());
            $lockedProposal->setRelation('potential', $lockedPotential);
            $this->guardTransition($lockedProposal->status, EProposalStatus::Published);

            $publishedAt = now();
            $lockedProposal->forceFill([
                'status' => EProposalStatus::Published,
                'published_at' => $publishedAt,
            ])->save();
            $lockedPotential->forceFill([
                'status' => PotensiStatus::Published,
                'published_at' => $publishedAt,
            ])->save();

            $this->recordReview($lockedProposal, $reviewer, EProposalStatus::Terverifikasi, EProposalStatus::Published, 'published');
            $this->recordActivity->execute('eproposal.published', $lockedProposal, ['status' => EProposalStatus::Published->value], $reviewer, $ipAddress);
            $this->notifySubmitter($lockedProposal, EProposalStatus::Published, 'Proposal telah dipublikasikan.');

            return $lockedProposal->load(['potential.village.kecamatan', 'potential.category', 'potential.bumdes', 'potential.media', 'submitter', 'reviews.reviewer']);
        });
    }

    /** @return array<string, list<EProposalStatus>> */
    public function allowedTransitions(): array
    {
        return [
            EProposalStatus::Diajukan->value => [EProposalStatus::PerluRevisi, EProposalStatus::Terverifikasi],
            EProposalStatus::PerluRevisi->value => [EProposalStatus::Diajukan],
            EProposalStatus::Terverifikasi->value => [EProposalStatus::Published],
            EProposalStatus::Published->value => [],
        ];
    }

    private function transition(
        EProposal $proposal,
        User $actor,
        EProposalStatus $to,
        string $action,
        ?string $note,
        ?string $ipAddress,
    ): EProposal {
        return DB::transaction(function () use ($proposal, $actor, $to, $action, $note, $ipAddress): EProposal {
            $lockedProposal = EProposal::query()->lockForUpdate()->findOrFail($proposal->getKey());
            $from = $lockedProposal->status;
            $this->guardTransition($from, $to);

            if ($to === EProposalStatus::PerluRevisi && blank($note)) {
                throw ValidationException::withMessages(['catatan' => 'Catatan revisi wajib diisi.']);
            }

            $attributes = ['status' => $to];
            if ($to === EProposalStatus::Diajukan) {
                $attributes['submitted_at'] = now();
                $attributes['catatan_pengaju'] = $note;
            }
            if ($to === EProposalStatus::Terverifikasi) {
                $attributes['verified_at'] = now();
            }

            $lockedProposal->forceFill($attributes)->save();
            $this->recordReview($lockedProposal, $actor, $from, $to, $action, $note);
            $this->recordActivity->execute('eproposal.'.$action, $lockedProposal, ['from' => $from->value, 'to' => $to->value], $actor, $ipAddress);

            $message = match ($to) {
                EProposalStatus::PerluRevisi => 'Proposal memerlukan revisi. Silakan periksa catatan Admin Kawasan.',
                EProposalStatus::Terverifikasi => 'Proposal telah terverifikasi.',
                EProposalStatus::Diajukan => 'Proposal berhasil dikirim ulang.',
                EProposalStatus::Published => 'Proposal telah dipublikasikan.',
            };
            $this->notifySubmitter($lockedProposal, $to, $message);

            return $lockedProposal->load(['potential.village.kecamatan', 'potential.category', 'submitter', 'reviews.reviewer']);
        });
    }

    private function guardTransition(EProposalStatus $from, EProposalStatus $to): void
    {
        $allowed = $this->allowedTransitions()[$from->value] ?? [];

        if (! in_array($to, $allowed, true)) {
            throw ValidationException::withMessages(['status' => sprintf('Perubahan status %s ke %s tidak diizinkan.', $from->label(), $to->label())]);
        }
    }

    private function recordReview(
        EProposal $proposal,
        User $actor,
        ?EProposalStatus $from,
        EProposalStatus $to,
        string $action,
        ?string $note = null,
    ): ReviewEProposal {
        return $proposal->reviews()->create([
            'reviewer_id' => $actor->getKey(),
            'status_sebelum' => $from?->value,
            'status_sesudah' => $to->value,
            'aksi' => $action,
            'catatan' => $note,
            'reviewed_at' => now(),
        ]);
    }

    private function notifySubmitter(EProposal $proposal, EProposalStatus $status, string $message): void
    {
        $proposal->loadMissing('submitter');
        $proposal->submitter?->notify(new EProposalStatusChanged($proposal, $status, $message));
    }
}
