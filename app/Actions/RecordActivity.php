<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RecordActivity
{
    /** @param array<string, mixed> $metadata */
    public function execute(
        string $action,
        ?Model $subject = null,
        array $metadata = [],
        ?User $user = null,
        ?string $ipAddress = null,
    ): ActivityLog {
        return ActivityLog::query()->create([
            'user_id' => $user?->getKey(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => $ipAddress,
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }
}
