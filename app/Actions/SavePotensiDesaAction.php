<?php

namespace App\Actions;

use App\Enums\PotensiStatus;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SavePotensiDesaAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor, ?PotensiDesa $potential = null, ?string $ipAddress = null): PotensiDesa
    {
        return DB::transaction(function () use ($data, $actor, $potential, $ipAddress): PotensiDesa {
            $isCreating = $potential === null;
            $record = $potential === null ? new PotensiDesa : PotensiDesa::query()->lockForUpdate()->findOrFail($potential->getKey());
            $before = $record->exists ? $record->only(['kategori_potensi_id', 'bumdes_id', 'status']) : [];
            $status = $actor->isSuperAdmin()
                ? PotensiStatus::from((string) ($data['status'] ?? $record->status?->value ?? PotensiStatus::Draft->value))
                : ($record->exists ? $record->status : PotensiStatus::Draft);

            if ($record->exists && $status !== $record->status && $record->eproposal()->exists()) {
                throw ValidationException::withMessages(['status' => 'Status potensi ini dikelola melalui workflow E-Proposal.']);
            }

            $record->fill([
                ...Arr::except($data, ['status']),
                'status' => $status,
                'published_at' => $this->publishedAt($record, $status),
            ]);
            $record->save();

            $this->recordActivity->execute(
                $isCreating ? 'potential.created' : 'potential.updated',
                $record,
                ['desa_id' => $record->desa_id, 'status' => $record->status->value],
                $actor,
                $ipAddress,
            );

            if (! $isCreating) {
                $this->recordRelationshipChanges($record, $before, $actor, $ipAddress);
            }

            return $record;
        });
    }

    private function publishedAt(PotensiDesa $potential, PotensiStatus $status): mixed
    {
        if ($status === PotensiStatus::Draft) {
            return null;
        }

        return $potential->status === PotensiStatus::Published
            ? $potential->published_at
            : now();
    }

    /** @param array<string, mixed> $before */
    private function recordRelationshipChanges(PotensiDesa $potential, array $before, User $actor, ?string $ipAddress): void
    {
        foreach ([
            'kategori_potensi_id' => 'potential.category.changed',
            'bumdes_id' => 'potential.bumdes.changed',
            'status' => 'potential.status.changed',
        ] as $attribute => $action) {
            $afterValue = $attribute === 'status' ? $potential->status->value : $potential->getAttribute($attribute);
            $beforeValue = $before[$attribute] instanceof PotensiStatus ? $before[$attribute]->value : ($before[$attribute] ?? null);

            if ((string) $beforeValue === (string) $afterValue) {
                continue;
            }

            $this->recordActivity->execute(
                $action,
                $potential,
                ['before' => $beforeValue, 'after' => $afterValue],
                $actor,
                $ipAddress,
            );
        }
    }
}
