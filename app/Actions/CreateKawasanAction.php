<?php

namespace App\Actions;

use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateKawasanAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor, ?string $ipAddress = null): KawasanPerdesaan
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress): KawasanPerdesaan {
            $kawasan = KawasanPerdesaan::query()->create(Arr::except($data, [
                'desa_ids',
                'category_ids',
                'admin_user_ids',
            ]));

            $desaIds = $data['desa_ids'];
            $categoryIds = $data['category_ids'];
            $adminUserIds = $data['admin_user_ids'] ?? [];

            $kawasan->desas()->sync($desaIds);
            $kawasan->categories()->sync($categoryIds);
            $kawasan->adminUsers()->sync($adminUserIds);

            $this->recordActivity->execute('kawasan.created', $kawasan, user: $actor, ipAddress: $ipAddress);
            $this->recordActivity->execute('kawasan.villages.synced', $kawasan, ['desa_ids' => $desaIds], $actor, $ipAddress);
            $this->recordActivity->execute('kawasan.categories.synced', $kawasan, ['category_ids' => $categoryIds], $actor, $ipAddress);
            $this->recordActivity->execute('kawasan.admins.synced', $kawasan, ['admin_user_ids' => $adminUserIds], $actor, $ipAddress);

            return $kawasan;
        });
    }
}
