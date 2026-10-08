<?php

namespace App\Actions;

use App\Enums\AchievementStatus;
use App\Models\Bumdes;
use App\Models\KawasanPerdesaan;
use App\Models\RealisasiAksi;
use App\Models\RencanaAksi;
use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BuildAchievementSummary
{
    public function __construct(private ActionAchievementCalculator $calculator) {}

    /** @return array<string, int|string|null> */
    public function forBumdes(User $user, int $bumdesId, int $year): array
    {
        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail($bumdesId);

        return $this->summarize($this->baseQuery($year)->where('bumdes_id', $bumdes->id)->get());
    }

    /** @return array<string, int|string|null> */
    public function forKawasan(User $user, int $kawasanId, int $year): array
    {
        $kawasan = KawasanPerdesaan::query()->visibleTo($user)->findOrFail($kawasanId);

        return $this->summarize(
            $this->baseQuery($year)
                ->whereHas('bumdes.desas.kawasans', fn (Builder $query) => $query->whereKey($kawasan->id))
                ->get(),
        );
    }

    /** @return array<string, int|string|null> */
    public function forKabupaten(User $user, int $year): array
    {
        abort_unless($user->isSuperAdmin(), 403);

        return $this->forUser($user, $year);
    }

    /** @return array<string, int|string|null> */
    public function forUser(User $user, int $year): array
    {
        return $this->summarize($this->baseQuery($year)->visibleTo($user)->get());
    }

    /**
     * @param  list<int>  $bumdesIds
     * @return Collection<int, array<string, int|string|null>>
     */
    public function forBumdeses(User $user, array $bumdesIds, int $year): Collection
    {
        $visibleIds = Bumdes::query()
            ->visibleTo($user)
            ->whereKey($bumdesIds)
            ->pluck('id');

        $plansByBumdes = $this->baseQuery($year)
            ->whereIn('bumdes_id', $visibleIds)
            ->get()
            ->groupBy('bumdes_id');

        return $visibleIds->mapWithKeys(fn (int $bumdesId): array => [
            $bumdesId => $this->summarize($plansByBumdes->get($bumdesId, new EloquentCollection)),
        ]);
    }

    /**
     * @param  list<int>  $kawasanIds
     * @return Collection<int, array<string, int|string|null>>
     */
    public function forKawasans(User $user, array $kawasanIds, int $year): Collection
    {
        $visibleIds = KawasanPerdesaan::query()
            ->visibleTo($user)
            ->whereKey($kawasanIds)
            ->pluck('id');

        $bumdesLinks = DB::table('kawasan_desa as kawasan_villages')
            ->join('bumdes_desa as bumdes_villages', 'bumdes_villages.desa_id', '=', 'kawasan_villages.desa_id')
            ->whereIn('kawasan_villages.kawasan_id', $visibleIds)
            ->select('kawasan_villages.kawasan_id', 'bumdes_villages.bumdes_id')
            ->distinct()
            ->get();

        $plansByBumdes = $this->baseQuery($year)
            ->whereIn('bumdes_id', $bumdesLinks->pluck('bumdes_id')->unique())
            ->get()
            ->groupBy('bumdes_id');

        return $visibleIds->mapWithKeys(function (int $kawasanId) use ($bumdesLinks, $plansByBumdes): array {
            $bumdesIds = $bumdesLinks
                ->where('kawasan_id', $kawasanId)
                ->pluck('bumdes_id')
                ->unique();
            $plans = new EloquentCollection(
                $bumdesIds->flatMap(fn (int $bumdesId): Collection => $plansByBumdes->get($bumdesId, new EloquentCollection))->all(),
            );

            return [$kawasanId => $this->summarize($plans)];
        });
    }

    /** @return array<string, int|string|null> */
    public function emptySummary(): array
    {
        return $this->summarize(new EloquentCollection);
    }

    /** @return Builder<RencanaAksi> */
    private function baseQuery(int $year): Builder
    {
        return RencanaAksi::query()
            ->whereHas('bumdes')
            ->where('tahun', $year)
            ->withCount('realizations')
            ->withSum('realizations', 'persentase_capaian')
            ->addSelect([
                'latest_status' => RealisasiAksi::query()
                    ->select('status')
                    ->whereColumn('rencana_aksi_id', 'rencana_aksi.id')
                    ->orderByDesc('periode_ke')
                    ->limit(1),
            ]);
    }

    /**
     * @param  EloquentCollection<int, RencanaAksi>  $plans
     * @return array<string, int|string|null>
     */
    private function summarize(EloquentCollection $plans): array
    {
        $counts = [
            AchievementStatus::NotStarted->value => 0,
            AchievementStatus::InProgress->value => 0,
            AchievementStatus::Completed->value => 0,
            AchievementStatus::Delayed->value => 0,
        ];
        $validPercentages = [];

        foreach ($plans as $plan) {
            $percentage = $plan->getAttribute('realizations_sum_persentase_capaian');
            $status = $this->calculator->status(
                (int) $plan->getAttribute('realizations_count'),
                $plan->getAttribute('latest_status'),
                $percentage,
            );
            $counts[$status->value]++;

            if ($percentage !== null) {
                $validPercentages[] = (string) $percentage;
            }
        }

        $average = null;

        if ($validPercentages !== []) {
            $sum = array_reduce(
                $validPercentages,
                fn (BigDecimal $carry, string $percentage): BigDecimal => $carry->plus($percentage),
                BigDecimal::zero(),
            );
            $average = (string) $sum->dividedBy(count($validPercentages), 2, RoundingMode::HalfUp);
        }

        return [
            'jumlah_rencana' => $plans->count(),
            'rata_rata_capaian' => $average,
            'jumlah_valid' => count($validPercentages),
            'jumlah_belum_mulai' => $counts[AchievementStatus::NotStarted->value],
            'jumlah_berjalan' => $counts[AchievementStatus::InProgress->value],
            'jumlah_selesai' => $counts[AchievementStatus::Completed->value],
            'jumlah_tertunda' => $counts[AchievementStatus::Delayed->value],
        ];
    }
}
