<?php

namespace App\Services;

use App\Actions\BuildAchievementSummary;
use App\Enums\EProposalStatus;
use App\Models\ActivityLog;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\EProposal;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardSummaryService
{
    /** @var list<string> */
    private const PROFILE_FIELDS = [
        'nomor_registrasi',
        'tahun_berdiri',
        'status_hukum',
        'alamat',
        'telepon',
        'email',
        'deskripsi',
    ];

    public function __construct(private BuildAchievementSummary $achievementSummary) {}

    /** @return array<string, mixed> */
    public function forUser(User $user, int $year): array
    {
        $defaults = [
            'year' => $year,
            'metrics' => [],
            'assignedDesas' => new EloquentCollection,
            'bumdesSummaries' => collect(),
            'kawasanSummaries' => collect(),
            'proposalStatusCounts' => $this->emptyProposalStatusCounts(),
            'achievement' => $this->achievementSummary->emptySummary(),
            'recentActivities' => new EloquentCollection,
        ];

        $roleData = match ($user->role) {
            UserRole::AdminDesa => $this->forAdminDesa($user, $year),
            UserRole::AdminKawasan => $this->forAdminKawasan($user, $year),
            UserRole::SuperAdmin => $this->forSuperAdmin($user, $year),
        };

        return array_replace($defaults, $roleData);
    }

    /** @return array<string, mixed> */
    private function forAdminDesa(User $user, int $year): array
    {
        $assignedDesas = $user->desas()
            ->with('kecamatan.kabupaten')
            ->orderBy('nama')
            ->orderBy('desa.id')
            ->get();
        $bumdes = Bumdes::query()
            ->visibleTo($user)
            ->withCount([
                'businessUnits',
                'actionPlans as action_plan_count' => fn ($query) => $query->where('tahun', $year),
            ])
            ->orderBy('nama')
            ->orderBy('id')
            ->get();
        $bumdesIds = $bumdes->modelKeys();
        $activeProducts = collect();

        if ($bumdesIds !== []) {
            $activeProducts = DB::table('produk_layanan as products')
                ->join('unit_usaha as units', 'units.id', '=', 'products.unit_usaha_id')
                ->whereIn('units.bumdes_id', $bumdesIds)
                ->where('products.is_active', true)
                ->selectRaw('units.bumdes_id, COUNT(*) as active_product_count')
                ->groupBy('units.bumdes_id')
                ->pluck('active_product_count', 'units.bumdes_id');
        }

        $achievementByBumdes = $this->achievementSummary->forBumdeses($user, $bumdesIds, $year);
        $proposals = EProposal::query()
            ->visibleTo($user)
            ->select('id', 'status')
            ->get();
        $proposalCounts = $this->countProposalStatuses($proposals);
        $bumdesSummaries = $bumdes->map(function (Bumdes $item) use ($activeProducts, $achievementByBumdes): array {
            $completedFields = collect(self::PROFILE_FIELDS)
                ->filter(fn (string $field): bool => filled($item->getAttribute($field)))
                ->count();

            return [
                'model' => $item,
                'profile_completed' => $completedFields,
                'profile_total' => count(self::PROFILE_FIELDS),
                'business_unit_count' => (int) $item->getAttribute('business_units_count'),
                'active_product_count' => (int) $activeProducts->get($item->id, 0),
                'action_plan_count' => (int) $item->getAttribute('action_plan_count'),
                'achievement' => $achievementByBumdes->get($item->id, $this->achievementSummary->emptySummary()),
            ];
        });

        return [
            'metrics' => [
                ['label' => 'Desa ditugaskan', 'value' => $assignedDesas->count(), 'meta' => 'sesuai kewenangan'],
                ['label' => 'BUMDes/BUMDesma', 'value' => $bumdes->count(), 'meta' => 'dalam desa tugas'],
                ['label' => 'Produk aktif', 'value' => $activeProducts->sum(), 'meta' => 'produk dan layanan'],
                ['label' => 'E-Proposal', 'value' => $proposals->count(), 'meta' => 'seluruh status'],
            ],
            'assignedDesas' => $assignedDesas,
            'bumdesSummaries' => $bumdesSummaries,
            'proposalStatusCounts' => $proposalCounts,
            'achievement' => $this->achievementSummary->forUser($user, $year),
        ];
    }

    /** @return array<string, mixed> */
    private function forAdminKawasan(User $user, int $year): array
    {
        $kawasans = $this->kawasanQuery($user)->get();
        $regionalData = $this->regionalData($user, $kawasans, $year);

        return [
            'metrics' => [
                ['label' => 'Kawasan ditugaskan', 'value' => $kawasans->count(), 'meta' => 'sesuai kewenangan'],
                ['label' => 'Desa', 'value' => $regionalData['village_count'], 'meta' => 'anggota kawasan'],
                ['label' => 'BUMDes/BUMDesma', 'value' => $regionalData['bumdes_count'], 'meta' => 'terhubung ke kawasan'],
                ['label' => 'Menunggu verifikasi', 'value' => $regionalData['proposal_counts'][EProposalStatus::Diajukan->value], 'meta' => 'proposal diajukan'],
            ],
            'kawasanSummaries' => $regionalData['rows'],
            'proposalStatusCounts' => $regionalData['proposal_counts'],
            'achievement' => $this->achievementSummary->forUser($user, $year),
        ];
    }

    /** @return array<string, mixed> */
    private function forSuperAdmin(User $user, int $year): array
    {
        $kawasans = $this->kawasanQuery($user)->get();
        $regionalData = $this->regionalData($user, $kawasans, $year);
        $achievement = $this->achievementSummary->forKabupaten($user, $year);

        return [
            'metrics' => [
                ['label' => 'Kawasan', 'value' => $kawasans->count(), 'meta' => 'seluruh status'],
                ['label' => 'Desa', 'value' => Desa::query()->count(), 'meta' => 'Kabupaten Madiun'],
                ['label' => 'BUMDes/BUMDesma', 'value' => Bumdes::query()->count(), 'meta' => 'lembaga terdata'],
                ['label' => 'Potensi published', 'value' => PotensiDesa::query()->published()->count(), 'meta' => 'tampil di katalog'],
                ['label' => 'Proposal published', 'value' => EProposal::query()->published()->count(), 'meta' => 'siap diakses publik'],
                ['label' => 'Rata-rata capaian', 'value' => $achievement['rata_rata_capaian'] === null ? '—' : $achievement['rata_rata_capaian'].'%', 'meta' => "rencana aksi {$year}"],
            ],
            'kawasanSummaries' => $regionalData['rows'],
            'proposalStatusCounts' => $regionalData['proposal_counts'],
            'achievement' => $achievement,
            'recentActivities' => ActivityLog::query()->with('user')->latest()->latest('id')->limit(6)->get(),
        ];
    }

    /** @return Builder<KawasanPerdesaan> */
    private function kawasanQuery(User $user): Builder
    {
        return KawasanPerdesaan::query()
            ->visibleTo($user)
            ->with('categories:id,nama')
            ->withCount('desas')
            ->orderBy('nama')
            ->orderBy('id');
    }

    /**
     * @param  EloquentCollection<int, KawasanPerdesaan>  $kawasans
     * @return array{rows: Collection<int, array<string, mixed>>, village_count: int, bumdes_count: int, proposal_counts: array<string, int>}
     */
    private function regionalData(User $user, EloquentCollection $kawasans, int $year): array
    {
        $kawasanIds = $kawasans->modelKeys();

        if ($kawasanIds === []) {
            return [
                'rows' => collect(),
                'village_count' => 0,
                'bumdes_count' => 0,
                'proposal_counts' => $this->emptyProposalStatusCounts(),
            ];
        }

        $villageLinks = DB::table('kawasan_desa')
            ->whereIn('kawasan_id', $kawasanIds)
            ->get(['kawasan_id', 'desa_id']);
        $villageIds = $villageLinks->pluck('desa_id')->unique()->values();
        $bumdesLinks = DB::table('bumdes_desa as links')
            ->join('bumdes', 'bumdes.id', '=', 'links.bumdes_id')
            ->whereIn('links.desa_id', $villageIds)
            ->whereNull('bumdes.deleted_at')
            ->select('links.desa_id', 'links.bumdes_id')
            ->distinct()
            ->get();
        $bumdesIds = $bumdesLinks->pluck('bumdes_id')->unique()->values();
        $bumdesById = Bumdes::query()
            ->visibleTo($user)
            ->whereKey($bumdesIds)
            ->select('id', 'nama', 'jenis')
            ->orderBy('nama')
            ->get()
            ->keyBy('id');
        $proposalRows = EProposal::query()
            ->visibleTo($user)
            ->join('potensi_desa as potentials', 'potentials.id', '=', 'eproposal.potensi_desa_id')
            ->whereNull('potentials.deleted_at')
            ->get(['eproposal.status', 'potentials.desa_id']);
        $proposalCounts = $this->countProposalStatuses($proposalRows);
        $achievementByKawasan = $this->achievementSummary->forKawasans($user, $kawasanIds, $year);
        $emptyAchievement = $this->achievementSummary->emptySummary();

        $rows = $kawasans->map(function (KawasanPerdesaan $kawasan) use (
            $villageLinks,
            $bumdesLinks,
            $bumdesById,
            $proposalRows,
            $achievementByKawasan,
            $emptyAchievement,
        ): array {
            $regionVillageIds = $villageLinks
                ->where('kawasan_id', $kawasan->id)
                ->pluck('desa_id')
                ->unique();
            $regionBumdesIds = $bumdesLinks
                ->whereIn('desa_id', $regionVillageIds)
                ->pluck('bumdes_id')
                ->unique();
            $regionProposals = $proposalRows->whereIn('desa_id', $regionVillageIds);

            return [
                'model' => $kawasan,
                'village_count' => $regionVillageIds->count(),
                'bumdes_count' => $regionBumdesIds->count(),
                'bumdes' => $regionBumdesIds->map(fn (int $bumdesId): ?Bumdes => $bumdesById->get($bumdesId))->filter()->sortBy('nama')->values(),
                'proposal_count' => $regionProposals->count(),
                'proposal_counts' => $this->countProposalStatuses($regionProposals),
                'achievement' => $achievementByKawasan->get($kawasan->id, $emptyAchievement),
            ];
        });

        return [
            'rows' => $rows,
            'village_count' => $villageIds->count(),
            'bumdes_count' => $bumdesById->count(),
            'proposal_counts' => $proposalCounts,
        ];
    }

    /** @param iterable<int, object> $proposals
     * @return array<string, int>
     */
    private function countProposalStatuses(iterable $proposals): array
    {
        $counts = $this->emptyProposalStatusCounts();

        foreach ($proposals as $proposal) {
            $status = $proposal->status instanceof EProposalStatus
                ? $proposal->status->value
                : (string) $proposal->status;
            $counts[$status]++;
        }

        return $counts;
    }

    /** @return array<string, int> */
    private function emptyProposalStatusCounts(): array
    {
        return collect(EProposalStatus::cases())
            ->mapWithKeys(fn (EProposalStatus $status): array => [$status->value => 0])
            ->all();
    }
}
