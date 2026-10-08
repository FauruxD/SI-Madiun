<?php

namespace App\Actions;

use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateKawasanAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    /** @param array<string, mixed> $data */
    public function execute(KawasanPerdesaan $kawasan, array $data, User $actor, ?string $ipAddress = null): KawasanPerdesaan
    {
        return DB::transaction(function () use ($kawasan, $data, $actor, $ipAddress): KawasanPerdesaan {
            $kawasan->fill(Arr::except($data, ['desa_ids', 'category_ids', 'admin_user_ids']));
            $changedFields = array_keys($kawasan->getDirty());
            $kawasan->save();

            $this->syncAndRecord($kawasan, 'desas', $data['desa_ids'], 'kawasan.villages.synced', 'desa_ids', $actor, $ipAddress);
            $this->syncAndRecord($kawasan, 'categories', $data['category_ids'], 'kawasan.categories.synced', 'category_ids', $actor, $ipAddress);
            $this->syncAndRecord($kawasan, 'adminUsers', $data['admin_user_ids'] ?? [], 'kawasan.admins.synced', 'admin_user_ids', $actor, $ipAddress);

            $this->recordActivity->execute(
                'kawasan.updated',
                $kawasan,
                ['changed_fields' => $changedFields],
                $actor,
                $ipAddress,
            );

            return $kawasan->refresh();
        });
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    private function syncAndRecord(
        KawasanPerdesaan $kawasan,
        string $relationship,
        array $ids,
        string $action,
        string $metadataKey,
        User $actor,
        ?string $ipAddress,
    ): void {
        $changes = $kawasan->{$relationship}()->sync($ids);

        if ($changes['attached'] === [] && $changes['detached'] === []) {
            return;
        }

        $this->recordActivity->execute($action, $kawasan, [
            $metadataKey => array_values($ids),
            'attached' => $changes['attached'],
            'detached' => $changes['detached'],
        ], $actor, $ipAddress);
    }
}
