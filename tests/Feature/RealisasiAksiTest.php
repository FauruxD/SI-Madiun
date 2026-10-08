<?php

namespace Tests\Feature;

use App\Enums\AchievementStatus;
use App\Enums\ActionPeriod;
use App\Models\ActivityLog;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KawasanPerdesaan;
use App\Models\RealisasiAksi;
use App\Models\RencanaAksi;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RealisasiAksiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_monthly_realization_can_be_created_and_is_logged(): void
    {
        [$admin, $bumdes, $plan] = $this->assignedPlan(ActionPeriod::Monthly);

        $this->actingAs($admin)
            ->post(route('bumdes.rencana-aksi.realisasi.store', [$bumdes, $plan]), $this->payload(12, '50.00'))
            ->assertRedirect();

        $realization = RealisasiAksi::query()->firstOrFail();
        $this->assertSame('50.00', $realization->persentase_capaian);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'action-realization.created', 'subject_id' => $realization->id]);
    }

    public function test_quarterly_realization_accepts_fourth_period(): void
    {
        [$admin, $bumdes, $plan] = $this->assignedPlan(ActionPeriod::Quarterly);

        $this->actingAs($admin)
            ->post(route('bumdes.rencana-aksi.realisasi.store', [$bumdes, $plan]), $this->payload(4, '25.00'))
            ->assertRedirect();

        $this->assertDatabaseHas(RealisasiAksi::class, ['rencana_aksi_id' => $plan->id, 'periode_ke' => 4]);
    }

    public function test_yearly_realization_accepts_first_period(): void
    {
        [$admin, $bumdes, $plan] = $this->assignedPlan(ActionPeriod::Yearly);

        $this->actingAs($admin)
            ->post(route('bumdes.rencana-aksi.realisasi.store', [$bumdes, $plan]), $this->payload(1, '100.00', AchievementStatus::Completed))
            ->assertRedirect();

        $this->assertDatabaseHas(RealisasiAksi::class, ['rencana_aksi_id' => $plan->id, 'periode_ke' => 1]);
    }

    public function test_duplicate_period_is_rejected_by_application_validation(): void
    {
        [$admin, $bumdes, $plan] = $this->assignedPlan(ActionPeriod::Monthly);
        $plan->realizations()->create($this->storedPayload(1));

        $this->actingAs($admin)
            ->post(route('bumdes.rencana-aksi.realisasi.store', [$bumdes, $plan]), $this->payload(1, '25.00'))
            ->assertInvalid(['periode_ke']);
    }

    public function test_period_incompatible_with_plan_frequency_is_rejected(): void
    {
        [$admin, $bumdes, $plan] = $this->assignedPlan(ActionPeriod::Quarterly);

        $this->actingAs($admin)
            ->post(route('bumdes.rencana-aksi.realisasi.store', [$bumdes, $plan]), $this->payload(5, '25.00'))
            ->assertInvalid(['periode_ke']);
    }

    public function test_admin_desa_cannot_edit_realization_from_other_bumdes(): void
    {
        [$admin] = $this->assignedPlan(ActionPeriod::Monthly);
        $otherPlan = RencanaAksi::factory()->create();
        $realization = $otherPlan->realizations()->create($this->storedPayload(1));

        $this->actingAs($admin)
            ->put(
                route('bumdes.rencana-aksi.realisasi.update', [$otherPlan->bumdes, $otherPlan, $realization]),
                $this->payload(1, '40.00'),
            )
            ->assertNotFound();
    }

    public function test_realization_child_id_from_another_plan_returns_not_found(): void
    {
        [$admin, $bumdes, $plan] = $this->assignedPlan(ActionPeriod::Monthly);
        $otherPlan = RencanaAksi::factory()->for($bumdes, 'bumdes')->create();
        $otherRealization = $otherPlan->realizations()->create($this->storedPayload(1));

        $this->actingAs($admin)
            ->put(
                route('bumdes.rencana-aksi.realisasi.update', [$bumdes, $plan, $otherRealization]),
                $this->payload(1, '40.00'),
            )
            ->assertNotFound();
    }

    public function test_database_constraint_rejects_duplicate_period(): void
    {
        [, , $plan] = $this->assignedPlan(ActionPeriod::Monthly);
        $plan->realizations()->create($this->storedPayload(1));

        $this->expectException(QueryException::class);
        $plan->realizations()->create($this->storedPayload(1));
    }

    public function test_realization_update_and_status_change_are_logged(): void
    {
        [$admin, $bumdes, $plan] = $this->assignedPlan(ActionPeriod::Monthly);
        $realization = $plan->realizations()->create($this->storedPayload(1));

        $this->actingAs($admin)
            ->put(
                route('bumdes.rencana-aksi.realisasi.update', [$bumdes, $plan, $realization]),
                $this->payload(1, '80.00', AchievementStatus::Delayed),
            )
            ->assertRedirect();

        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'action-realization.updated', 'subject_id' => $realization->id]);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'action-realization.status-changed', 'subject_id' => $realization->id]);
    }

    public function test_changing_numeric_target_recalculates_existing_percentages(): void
    {
        [$admin, $bumdes, $plan] = $this->assignedPlan(ActionPeriod::Monthly);
        $realization = $plan->realizations()->create($this->storedPayload(1, '50.00', '50.00'));

        $this->actingAs($admin)
            ->put(route('bumdes.rencana-aksi.update', [$bumdes, $plan]), $this->planPayload('200.00'))
            ->assertRedirect();

        $this->assertSame('25.00', $realization->refresh()->persentase_capaian);
    }

    public function test_admin_kawasan_has_read_only_access_to_realizations(): void
    {
        $admin = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $desa = Desa::factory()->create();
        $bumdes = Bumdes::factory()->create();
        $kawasan->adminUsers()->attach($admin);
        $kawasan->desas()->attach($desa);
        $bumdes->desas()->attach($desa, ['is_desa_utama' => true]);
        $plan = RencanaAksi::factory()->for($bumdes, 'bumdes')->create();

        $this->actingAs($admin)->get(route('bumdes.rencana-aksi.show', [$bumdes, $plan]))->assertOk();
        $this->actingAs($admin)
            ->post(route('bumdes.rencana-aksi.realisasi.store', [$bumdes, $plan]), $this->payload(1, '10.00'))
            ->assertForbidden();
    }

    /** @return array<string, mixed> */
    private function payload(int $period, string $value, AchievementStatus $status = AchievementStatus::InProgress): array
    {
        return [
            'periode_ke' => $period,
            'realisasi_nilai' => $value,
            'catatan' => 'Capaian periode berjalan.',
            'tanggal_realisasi' => now()->format('Y-m-d'),
            'status' => $status->value,
        ];
    }

    /** @return array<string, mixed> */
    private function storedPayload(int $period, ?string $value = '25.00', ?string $percentage = '25.00'): array
    {
        return [
            'periode_ke' => $period,
            'realisasi_nilai' => $value,
            'catatan' => null,
            'tanggal_realisasi' => now()->format('Y-m-d'),
            'persentase_capaian' => $percentage,
            'status' => AchievementStatus::InProgress,
        ];
    }

    /** @return array<string, mixed> */
    private function planPayload(string $target): array
    {
        return [
            'tahun' => now()->year,
            'program' => 'Program penguatan pasar',
            'kegiatan' => 'Distribusi produk',
            'target_deskripsi' => null,
            'target_nilai' => $target,
            'target_satuan' => 'unit',
            'periode' => ActionPeriod::Monthly->value,
            'tanggal_mulai' => null,
            'tanggal_selesai' => null,
        ];
    }

    /** @return array{User, Bumdes, RencanaAksi} */
    private function assignedPlan(ActionPeriod $period): array
    {
        $admin = User::factory()->adminDesa()->create();
        $desa = Desa::factory()->create();
        $admin->desas()->attach($desa);
        $bumdes = Bumdes::factory()->create();
        $bumdes->desas()->attach($desa, ['is_desa_utama' => true]);
        $plan = RencanaAksi::factory()->for($bumdes, 'bumdes')->create([
            'periode' => $period,
            'target_nilai' => '100.00',
            'tahun' => now()->year,
        ]);

        return [$admin, $bumdes, $plan];
    }
}
