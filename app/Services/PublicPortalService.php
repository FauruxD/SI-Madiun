<?php

namespace App\Services;

use App\Enums\StatusKemitraan;
use App\Models\EProposal;
use App\Models\KategoriPotensi;
use App\Models\KawasanPerdesaan;
use App\Models\KemitraanBumdes;
use App\Models\PotensiDesa;
use App\Models\WebsiteBumdes;

class PublicPortalService
{
    public function __construct(private MapFeatureService $mapFeatures) {}

    /** @return array<string, mixed> */
    public function home(): array
    {
        return [
            'statistics' => [
                'kawasans' => KawasanPerdesaan::query()->established()->count(),
                'potentials' => PotensiDesa::query()->published()->count(),
                'bumdesWebsites' => WebsiteBumdes::query()->active()->count(),
                'proposals' => EProposal::query()->published()->count(),
            ],
            'categories' => KategoriPotensi::query()
                ->whereHas('potentials', fn ($query) => $query->published())
                ->orderBy('nama')
                ->get(),
            'potentials' => PotensiDesa::query()
                ->published()
                ->with(['category', 'village.kecamatan', 'coverImage'])
                ->latest('published_at')
                ->latest('id')
                ->limit(4)
                ->get(),
            'mapCollection' => $this->mapFeatures->collection(),
            'kawasans' => KawasanPerdesaan::query()
                ->established()
                ->with('categories')
                ->withCount('desas')
                ->orderByDesc('tanggal_penetapan')
                ->orderBy('nama')
                ->limit(4)
                ->get(),
            'websites' => WebsiteBumdes::query()
                ->active()
                ->with(['bumdes.desas' => fn ($query) => $query->orderByPivot('is_desa_utama', 'desc')->orderBy('nama')])
                ->latest('activated_at')
                ->latest('id')
                ->limit(4)
                ->get(),
            'opportunities' => KemitraanBumdes::query()
                ->where('status', StatusKemitraan::Dibutuhkan->value)
                ->whereHas('bumdes.website', fn ($query) => $query->active())
                ->with(['bumdes.website', 'bumdes.desas' => fn ($query) => $query->orderByPivot('is_desa_utama', 'desc')->orderBy('nama')])
                ->latest('id')
                ->limit(3)
                ->get(),
            'proposals' => EProposal::query()
                ->published()
                ->with(['potential.category', 'potential.village'])
                ->latest('published_at')
                ->latest('id')
                ->limit(3)
                ->get(),
        ];
    }
}
