<?php

namespace App\Services;

use App\Models\Bumdes;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class MapEditorScope
{
    public const KINDS = ['kawasan' => 'Kawasan perdesaan', 'bumdes' => 'Kantor BUMDes', 'potensi' => 'Potensi desa'];

    /** @return Builder<KawasanPerdesaan>|Builder<Bumdes>|Builder<PotensiDesa> */
    public function query(string $kind, User $user): Builder
    {
        return match ($kind) {
            'kawasan' => KawasanPerdesaan::query()->visibleTo($user),
            'bumdes' => Bumdes::query()->visibleTo($user),
            'potensi' => PotensiDesa::query()->visibleTo($user),
            default => abort(404),
        };
    }

    public function find(string $kind, int $id, User $user): KawasanPerdesaan|Bumdes|PotensiDesa
    {
        $entity = $this->query($kind, $user)->findOrFail($id);
        Gate::forUser($user)->authorize('manageMap', $entity);

        return $entity;
    }
}
