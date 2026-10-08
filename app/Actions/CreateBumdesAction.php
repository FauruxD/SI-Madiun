<?php

namespace App\Actions;

use App\Models\Bumdes;
use App\Models\User;
use App\Models\WebsiteBumdes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateBumdesAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor, ?string $ipAddress = null): Bumdes
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress): Bumdes {
            $bumdes = Bumdes::query()->create(Arr::except($data, ['desa_ids', 'desa_utama_id']));
            $villages = $this->villagePivot($data['desa_ids'], (int) $data['desa_utama_id']);
            $bumdes->desas()->sync($villages);
            $bumdes->website()->create(['subdomain' => WebsiteBumdes::defaultSubdomain($bumdes)]);

            $this->recordActivity->execute('bumdes.created', $bumdes, user: $actor, ipAddress: $ipAddress);
            $this->recordActivity->execute('bumdes.villages.synced', $bumdes, [
                'desa_ids' => array_map('intval', $data['desa_ids']),
                'desa_utama_id' => (int) $data['desa_utama_id'],
            ], $actor, $ipAddress);

            return $bumdes;
        });
    }

    /**
     * @param  array<int, int|string>  $villageIds
     * @return array<int, array{is_desa_utama: bool}>
     */
    private function villagePivot(array $villageIds, int $primaryVillageId): array
    {
        return collect($villageIds)->mapWithKeys(fn (int|string $id): array => [
            (int) $id => ['is_desa_utama' => (int) $id === $primaryVillageId],
        ])->all();
    }
}
