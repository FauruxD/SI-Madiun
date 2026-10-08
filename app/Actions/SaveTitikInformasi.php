<?php

namespace App\Actions;

use App\Models\KawasanPerdesaan;
use App\Models\TitikInformasi;
use App\Models\User;
use App\Rules\GeoJsonGeometry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveTitikInformasi
{
    public function __construct(private RecordActivity $recordActivity) {}

    /** @param array<string, mixed> $data */
    public function execute(KawasanPerdesaan $kawasan, ?int $pointId, array $data, User $actor, ?string $ipAddress = null): TitikInformasi
    {
        return DB::transaction(function () use ($kawasan, $pointId, $data, $actor, $ipAddress): TitikInformasi {
            $locked = KawasanPerdesaan::query()->lockForUpdate()->findOrFail($kawasan->getKey());
            Gate::forUser($actor)->authorize('manageMap', $locked);
            if ($locked->informationPoints()->where('nama', $data['nama'])->where('jenis', $data['jenis'])
                ->when($pointId !== null, fn ($query) => $query->whereKeyNot($pointId))->exists()) {
                throw ValidationException::withMessages(['nama' => 'Titik dengan nama dan jenis ini sudah tersedia di kawasan.']);
            }
            $point = $pointId === null ? $locked->informationPoints()->make() : $locked->informationPoints()->lockForUpdate()->findOrFail($pointId);
            $point->fill([
                'nama' => $data['nama'], 'jenis' => $data['jenis'], 'deskripsi' => $data['deskripsi'] ?? null,
                'is_active' => $data['is_active'], 'geometri' => (new GeoJsonGeometry)->normalize($data['geometri']),
            ])->save();
            $this->recordActivity->execute($pointId === null ? 'map.information.created' : 'map.information.updated', $point, ['kawasan_id' => $locked->id], $actor, $ipAddress);

            return $point;
        });
    }
}
