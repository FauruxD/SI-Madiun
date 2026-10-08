<?php

namespace App\Actions;

use App\Models\Bumdes;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\User;
use App\Rules\GeoJsonGeometry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SaveMapGeometry
{
    public function __construct(private RecordActivity $recordActivity) {}

    public function execute(KawasanPerdesaan|Bumdes|PotensiDesa $entity, mixed $geometry, User $actor, ?string $ipAddress = null): void
    {
        DB::transaction(function () use ($entity, $geometry, $actor, $ipAddress): void {
            $locked = $entity->newQuery()->lockForUpdate()->findOrFail($entity->getKey());
            Gate::forUser($actor)->authorize('manageMap', $locked);
            $locked->geometri = $geometry === null ? null : (new GeoJsonGeometry($entity instanceof KawasanPerdesaan ? 'Polygon' : 'Point'))->normalize($geometry);
            $locked->save();
            $this->recordActivity->execute('map.geometry.updated', $locked, ['has_geometry' => $locked->geometri !== null], $actor, $ipAddress);
        });
    }
}
