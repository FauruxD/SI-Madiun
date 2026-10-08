<?php

namespace App\Actions;

use App\Enums\AchievementStatus;
use App\Models\RealisasiAksi;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Collection;

class ActionAchievementCalculator
{
    public function percentage(int|string|null $target, int|string|null $realization): ?string
    {
        if ($target === null || $realization === null) {
            return null;
        }

        $targetValue = BigDecimal::of($target);

        if ($targetValue->isLessThanOrEqualTo(BigDecimal::zero())) {
            return null;
        }

        return (string) BigDecimal::of($realization)
            ->multipliedBy(100)
            ->dividedBy($targetValue, 2, RoundingMode::HalfUp);
    }

    /**
     * @param  iterable<int, RealisasiAksi>  $realizations
     * @return array{total_realisasi: ?string, persentase_capaian: ?string, status: AchievementStatus, periode_terisi: int}
     */
    public function summarize(int|string|null $target, iterable $realizations): array
    {
        $items = $realizations instanceof Collection ? $realizations : collect($realizations);
        $numericValues = $items->pluck('realisasi_nilai')->filter(fn (mixed $value): bool => $value !== null);
        $total = $numericValues->isEmpty()
            ? null
            : (string) $numericValues->reduce(
                fn (BigDecimal $sum, int|string $value): BigDecimal => $sum->plus($value),
                BigDecimal::zero(),
            )->toScale(2);
        $percentage = $this->percentage($target, $total);
        $latestStatus = $items
            ->sortByDesc(fn (RealisasiAksi $realization): int => $realization->periode_ke)
            ->first()?->status;

        return [
            'total_realisasi' => $total,
            'persentase_capaian' => $percentage,
            'status' => $this->status($items->count(), $latestStatus, $percentage),
            'periode_terisi' => $items->count(),
        ];
    }

    public function status(
        int $realizationCount,
        AchievementStatus|string|null $latestStatus,
        int|string|null $percentage,
    ): AchievementStatus {
        if ($realizationCount === 0) {
            return AchievementStatus::NotStarted;
        }

        $status = is_string($latestStatus) ? AchievementStatus::tryFrom($latestStatus) : $latestStatus;

        if ($status === AchievementStatus::Delayed) {
            return AchievementStatus::Delayed;
        }

        if ($status === AchievementStatus::Completed
            || ($percentage !== null && BigDecimal::of($percentage)->isGreaterThanOrEqualTo('100'))) {
            return AchievementStatus::Completed;
        }

        return AchievementStatus::InProgress;
    }
}
