<?php

namespace Tests\Unit;

use App\Actions\ActionAchievementCalculator;
use App\Enums\AchievementStatus;
use App\Models\RealisasiAksi;
use PHPUnit\Framework\TestCase;

class ActionAchievementCalculatorTest extends TestCase
{
    private ActionAchievementCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new ActionAchievementCalculator;
    }

    public function test_zero_realization_produces_zero_percent(): void
    {
        $this->assertSame('0.00', $this->calculator->percentage('100.00', '0.00'));
    }

    public function test_half_realization_produces_fifty_percent(): void
    {
        $this->assertSame('50.00', $this->calculator->percentage('100.00', '50.00'));
    }

    public function test_equal_realization_produces_one_hundred_percent(): void
    {
        $this->assertSame('100.00', $this->calculator->percentage('100.00', '100.00'));
    }

    public function test_overachievement_is_not_capped(): void
    {
        $this->assertSame('120.00', $this->calculator->percentage('100.00', '120.00'));
    }

    public function test_zero_target_cannot_be_calculated(): void
    {
        $this->assertNull($this->calculator->percentage('0.00', '50.00'));
    }

    public function test_null_target_cannot_be_calculated(): void
    {
        $this->assertNull($this->calculator->percentage(null, '50.00'));
    }

    public function test_null_realization_is_not_invented_as_zero(): void
    {
        $this->assertNull($this->calculator->percentage('100.00', null));
    }

    public function test_multiple_incremental_realizations_are_aggregated_precisely(): void
    {
        $snapshot = $this->calculator->summarize('100.00', [
            $this->realization(1, '20.10'),
            $this->realization(2, '30.20'),
        ]);

        $this->assertSame('50.30', $snapshot['total_realisasi']);
        $this->assertSame('50.30', $snapshot['persentase_capaian']);
        $this->assertSame(2, $snapshot['periode_terisi']);
    }

    public function test_plan_without_realization_is_not_started(): void
    {
        $snapshot = $this->calculator->summarize('100.00', []);

        $this->assertSame(AchievementStatus::NotStarted, $snapshot['status']);
    }

    public function test_plan_with_unfinished_realization_is_in_progress(): void
    {
        $snapshot = $this->calculator->summarize('100.00', [$this->realization(1, '50.00')]);

        $this->assertSame(AchievementStatus::InProgress, $snapshot['status']);
    }

    public function test_manual_completed_status_completes_descriptive_target(): void
    {
        $snapshot = $this->calculator->summarize(null, [
            $this->realization(1, null, AchievementStatus::Completed),
        ]);

        $this->assertSame(AchievementStatus::Completed, $snapshot['status']);
        $this->assertNull($snapshot['persentase_capaian']);
    }

    public function test_latest_delayed_status_takes_priority(): void
    {
        $snapshot = $this->calculator->summarize('100.00', [
            $this->realization(1, '100.00', AchievementStatus::Completed),
            $this->realization(2, null, AchievementStatus::Delayed),
        ]);

        $this->assertSame(AchievementStatus::Delayed, $snapshot['status']);
    }

    private function realization(
        int $period,
        ?string $value,
        AchievementStatus $status = AchievementStatus::InProgress,
    ): RealisasiAksi {
        return new RealisasiAksi([
            'periode_ke' => $period,
            'realisasi_nilai' => $value,
            'status' => $status,
        ]);
    }
}
