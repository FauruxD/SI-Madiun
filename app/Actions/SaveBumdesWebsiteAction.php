<?php

namespace App\Actions;

use App\Models\Bumdes;
use App\Models\User;
use App\Models\WebsiteBumdes;
use Illuminate\Support\Facades\DB;

class SaveBumdesWebsiteAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    /** @param array{subdomain: string, custom_domain?: ?string, is_active: bool|int|string} $data */
    public function execute(Bumdes $bumdes, array $data, User $actor, ?string $ipAddress): WebsiteBumdes
    {
        return DB::transaction(function () use ($bumdes, $data, $actor, $ipAddress): WebsiteBumdes {
            $profile = Bumdes::query()->lockForUpdate()->findOrFail($bumdes->id);
            $website = $profile->website()->firstOrNew();
            $active = (bool) $data['is_active'];
            $website->fill([
                'subdomain' => $data['subdomain'],
                'custom_domain' => $data['custom_domain'] ?? null,
                'is_active' => $active,
                'activated_at' => $active ? ($website->activated_at ?? now()) : null,
            ]);
            $website->save();
            $this->recordActivity->execute('bumdes.website.updated', $profile, [
                'subdomain' => $website->subdomain,
                'custom_domain' => $website->custom_domain,
                'is_active' => $website->is_active,
            ], $actor, $ipAddress);

            return $website;
        });
    }
}
