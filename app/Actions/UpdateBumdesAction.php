<?php

namespace App\Actions;

use App\Models\Bumdes;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateBumdesAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    /** @param array<string, mixed> $data */
    public function execute(Bumdes $bumdes, array $data, User $actor, ?string $ipAddress = null): Bumdes
    {
        return DB::transaction(function () use ($bumdes, $data, $actor, $ipAddress): Bumdes {
            $bumdes->fill(Arr::except($data, ['desa_ids', 'desa_utama_id']));
            $changedFields = array_keys($bumdes->getDirty());
            $bumdes->save();

            if ($actor->isSuperAdmin() && array_key_exists('desa_ids', $data)) {
                $this->syncVillages($bumdes, $data, $actor, $ipAddress);
            }

            $this->recordActivity->execute('bumdes.updated', $bumdes, [
                'changed_fields' => $changedFields,
            ], $actor, $ipAddress);

            return $bumdes->refresh();
        });
    }

    /** @param array<string, mixed> $data */
    private function syncVillages(Bumdes $bumdes, array $data, User $actor, ?string $ipAddress): void
    {
        $primaryVillageId = (int) $data['desa_utama_id'];
        $villages = collect($data['desa_ids'])->mapWithKeys(fn (int|string $id): array => [
            (int) $id => ['is_desa_utama' => (int) $id === $primaryVillageId],
        ])->all();
        $changes = $bumdes->desas()->sync($villages);

        if ($changes['attached'] === [] && $changes['detached'] === [] && $changes['updated'] === []) {
            return;
        }

        $this->recordActivity->execute('bumdes.villages.synced', $bumdes, [
            'desa_ids' => array_map('intval', $data['desa_ids']),
            'desa_utama_id' => $primaryVillageId,
            'attached' => $changes['attached'],
            'detached' => $changes['detached'],
        ], $actor, $ipAddress);
    }
}
