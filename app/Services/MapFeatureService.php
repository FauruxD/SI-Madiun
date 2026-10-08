<?php

namespace App\Services;

use App\Enums\KawasanStatus;
use App\Enums\StatusUnitUsaha;
use App\Models\Bumdes;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\TitikInformasi;
use App\Models\User;
use App\Rules\GeoJsonGeometry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class MapFeatureService
{
    /** @return array{type: string, features: list<array>, meta: array} */
    public function collection(?User $user = null): array
    {
        $limit = max(1, min(250, (int) config('map.max_features_per_layer', 100)));
        $features = [];
        $truncated = [];
        $counts = [];

        $queries = [
            'kawasan' => KawasanPerdesaan::query()->with('categories:id,nama')->when($user,
                fn (Builder $query) => $query->visibleTo($user),
                fn (Builder $query) => $query->where('status', KawasanStatus::Ditetapkan->value)),
            'bumdes' => Bumdes::query()->with([
                'website:id,bumdes_id,subdomain,is_active',
                'businessUnits' => fn (HasMany $query) => $query->where('status', StatusUnitUsaha::Aktif->value)->select('id', 'bumdes_id', 'nama')->orderBy('id')->limit(3),
            ])->when($user, fn (Builder $query) => $query->visibleTo($user)),
            'informasi' => TitikInformasi::query()->where('is_active', true)->whereHas('kawasan',
                fn (Builder $query) => $user ? $query->visibleTo($user) : $query->where('status', KawasanStatus::Ditetapkan->value)),
            'potensi' => PotensiDesa::query()->with('village:id,nama', 'category:id,nama')->whereHas('village')->when($user,
                fn (Builder $query) => $query->visibleTo($user),
                fn (Builder $query) => $query->published()),
        ];

        foreach ($queries as $layer => $query) {
            $items = $query->whereNotNull('geometri')->orderBy('id')->limit($limit + 1)->get();
            if ($items->count() > $limit) {
                $truncated[] = $layer;
            }
            $counts[$layer] = 0;
            foreach ($items->take($limit) as $item) {
                try {
                    $geometry = (new GeoJsonGeometry($layer === 'kawasan' ? 'Polygon' : 'Point'))->normalize($item->geometri);
                } catch (InvalidArgumentException) {
                    continue;
                }
                $features[] = [
                    'type' => 'Feature', 'id' => $layer.'-'.$item->id, 'geometry' => $geometry,
                    'properties' => $this->properties($layer, $item, $user !== null),
                ];
                $counts[$layer]++;
            }
        }

        return ['type' => 'FeatureCollection', 'features' => $features, 'meta' => ['counts' => $counts, 'truncated' => $truncated]];
    }

    /** @return array{layer: string, name: string, summary: string, url: ?string, color: string, categories: array} */
    private function properties(string $layer, Model $item, bool $internal): array
    {
        $categories = [];
        $url = null;
        $color = '#475569';
        $summary = '';
        if ($item instanceof KawasanPerdesaan) {
            $categories = $item->categories->sortBy('nama')->map(fn (Model $category): array => [
                'name' => $category->nama, 'color' => ['#047857', '#b45309', '#0369a1', '#a21caf', '#be123c', '#4d7c0f'][crc32($category->nama) % 6],
            ])->values()->all();
            $color = $categories[0]['color'] ?? '#475569';
            $summary = count($categories) ? implode(' · ', array_column($categories, 'name')) : 'Kategori belum ditambahkan';
        } elseif ($item instanceof Bumdes) {
            $color = '#0369a1';
            $summary = $item->jenis->label().($item->businessUnits->isNotEmpty() ? ' · '.$item->businessUnits->pluck('nama')->implode(', ') : '');
            if ($item->website?->is_active) {
                $url = route('public.bumdes.show', $item->website->subdomain, false);
            }
        } elseif ($item instanceof PotensiDesa) {
            $color = '#b45309';
            $summary = ($item->village?->nama ?? '').' · '.($item->category?->nama ?? '');
            $url = route($internal ? 'potensi-desa.show' : 'katalog-potensi.show', $item->id, false);
        } elseif ($item instanceof TitikInformasi) {
            $color = '#475569';
            $summary = (TitikInformasi::TYPES[$item->jenis] ?? 'Informasi').' · '.($item->deskripsi ?? '');
        }

        return [
            'layer' => $layer, 'name' => $item instanceof PotensiDesa ? $item->judul : $item->nama,
            'summary' => $summary, 'url' => $url, 'color' => $color, 'categories' => $categories,
        ];
    }
}
