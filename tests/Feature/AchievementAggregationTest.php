<?php

namespace Tests\Feature;

use App\Actions\BuildAchievementSummary;
use App\Enums\AchievementStatus;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KawasanPerdesaan;
use App\Models\RencanaAksi;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AchievementAggregationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_bumdes_summary_counts_plans_statuses_and_valid_average(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $bumdes = Bumdes::factory()->create();
        $running = $this->plan($bumdes);
        $notStarted = $this->plan($bumdes);
        $completed = $this->plan($bumdes);
        $this->realization($running, 1, '75.00', '75.00');
        $this->realization($completed, 1, '100.00', '100.00');

        $summary = app(BuildAchievementSummary::class)->forBumdes($superAdmin, $bumdes->id, now()->year);

        $this->assertSame(3, $summary['jumlah_rencana']);
        $this->assertSame('87.50', $summary['rata_rata_capaian']);
        $this->assertSame(1, $summary['jumlah_belum_mulai']);
        $this->assertSame(1, $summary['jumlah_berjalan']);
        $this->assertSame(1, $summary['jumlah_selesai']);
        $this->assertSame(0, $summary['jumlah_tertunda']);
        $this->assertNotNull($notStarted);
    }

    public function test_kawasan_summary_only_includes_bumdes_inside_the_kawasan(): void
    {
        $admin = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $desa = Desa::factory()->create();
        $inside = Bumdes::factory()->create();
        $outside = Bumdes::factory()->create();
        $kawasan->adminUsers()->attach($admin);
        $kawasan->desas()->attach($desa);
        $inside->desas()->attach($desa, ['is_desa_utama' => true]);
        $insidePlan = $this->plan($inside);
        $outsidePlan = $this->plan($outside);
        $this->realization($insidePlan, 1, '50.00', '50.00');
        $this->realization($outsidePlan, 1, '100.00', '100.00');

        $summary = app(BuildAchievementSummary::class)->forKawasan($admin, $kawasan->id, now()->year);

        $this->assertSame(1, $summary['jumlah_rencana']);
        $this->assertSame('50.00', $summary['rata_rata_capaian']);
    }

    public function test_pmd_summary_includes_all_bumdes_for_selected_year(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $first = $this->plan(Bumdes::factory()->create());
        $second = $this->plan(Bumdes::factory()->create());
        $old = $this->plan(Bumdes::factory()->create(), now()->year - 1);
        $this->realization($first, 1, '50.00', '50.00');
        $this->realization($second, 1, '100.00', '100.00');
        $this->realization($old, 1, '10.00', '10.00');

        $summary = app(BuildAchievementSummary::class)->forKabupaten($superAdmin, now()->year);

        $this->assertSame(2, $summary['jumlah_rencana']);
        $this->assertSame('75.00', $summary['rata_rata_capaian']);
    }

    public function test_null_achievement_is_excluded_instead_of_counted_as_zero(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $bumdes = Bumdes::factory()->create();
        $numeric = $this->plan($bumdes);
        $descriptive = RencanaAksi::factory()->for($bumdes, 'bumdes')->descriptive()->create(['tahun' => now()->year]);
        $this->realization($numeric, 1, '50.00', '50.00');
        $this->realization($descriptive, 1, null, null, AchievementStatus::Completed);

        $summary = app(BuildAchievementSummary::class)->forBumdes($superAdmin, $bumdes->id, now()->year);

        $this->assertSame(2, $summary['jumlah_rencana']);
        $this->assertSame(1, $summary['jumlah_valid']);
        $this->assertSame('50.00', $summary['rata_rata_capaian']);
        $this->assertSame(1, $summary['jumlah_selesai']);
    }

    public function test_admin_kawasan_cannot_request_summary_for_another_kawasan(): void
    {
        $admin = User::factory()->adminKawasan()->create();
        $assigned = KawasanPerdesaan::factory()->create();
        $other = KawasanPerdesaan::factory()->create();
        $assigned->adminUsers()->attach($admin);

        $this->expectException(ModelNotFoundException::class);
        app(BuildAchievementSummary::class)->forKawasan($admin, $other->id, now()->year);
    }

    private function plan(Bumdes $bumdes, ?int $year = null): RencanaAksi
    {
        return RencanaAksi::factory()->for($bumdes, 'bumdes')->create([
            'tahun' => $year ?? now()->year,
            'target_nilai' => '100.00',
        ]);
    }

    private function realization(
        RencanaAksi $plan,
        int $period,
        ?string $value,
        ?string $percentage,
        AchievementStatus $status = AchievementStatus::InProgress,
    ): void {
        $plan->realizations()->create([
            'periode_ke' => $period,
            'realisasi_nilai' => $value,
            'catatan' => null,
            'tanggal_realisasi' => now(),
            'persentase_capaian' => $percentage,
            'status' => $status,
        ]);
    }
}
