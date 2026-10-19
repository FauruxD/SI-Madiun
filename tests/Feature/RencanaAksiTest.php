<?php

namespace Tests\Feature;

use App\Enums\ActionPeriod;
use App\Models\ActivityLog;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KawasanPerdesaan;
use App\Models\RealisasiAksi;
use App\Models\RencanaAksi;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RencanaAksiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_desa_can_create_action_plan_for_owned_bumdes_and_activity_is_recorded(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)
            ->post(route('bumdes.rencana-aksi.store', $bumdes), $this->payload())
            ->assertRedirect();

        $plan = RencanaAksi::query()->firstOrFail();
        $this->assertSame($bumdes->id, $plan->bumdes_id);
        $this->assertSame(ActionPeriod::Monthly, $plan->periode);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'action-plan.created', 'subject_id' => $plan->id]);
    }

    public function test_admin_desa_cannot_create_action_plan_for_other_bumdes(): void
    {
        [$admin] = $this->assigned();
        $other = Bumdes::factory()->create();

        $this->actingAs($admin)
            ->post(route('bumdes.rencana-aksi.store', $other), $this->payload())
            ->assertNotFound();
    }

    public function test_super_admin_can_view_all_action_plans(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        RencanaAksi::factory()->create(['program' => 'Program Kawasan Utara', 'tahun' => now()->year]);
        RencanaAksi::factory()->create(['program' => 'Program Kawasan Selatan', 'tahun' => now()->year]);

        $this->actingAs($superAdmin)
            ->get(route('rencana-aksi.index'))
            ->assertOk()
            ->assertSeeText('Program Kawasan Utara')
            ->assertSeeText('Program Kawasan Selatan');
    }

    public function test_admin_desa_can_open_plan_input_for_an_owned_bumdes_from_the_matrix(): void
    {
        [$admin, $ownedBumdes] = $this->assigned();
        $ownedBumdes->update(['nama' => 'BUMDes Amanah Milik Admin']);
        $otherBumdes = Bumdes::factory()->create(['nama' => 'BUMDes Di Luar Kewenangan']);

        $this->actingAs($admin)
            ->get(route('rencana-aksi.index', ['bumdes_id' => $ownedBumdes->id, 'tahun' => now()->year]))
            ->assertOk()
            ->assertSeeText('2. Input rencana kerja tahunan')
            ->assertSeeText('BUMDes Amanah Milik Admin')
            ->assertSee('action="'.route('bumdes.rencana-aksi.store', $ownedBumdes).'"', false)
            ->assertSeeText('Simpan & lanjut input realisasi')
            ->assertDontSeeText('BUMDes Di Luar Kewenangan');

        $this->assertDatabaseHas(Bumdes::class, ['id' => $otherBumdes->id]);
    }

    public function test_admin_desa_cannot_select_an_unassigned_bumdes_from_the_plan_matrix(): void
    {
        [$admin] = $this->assigned();
        $otherBumdes = Bumdes::factory()->create(['nama' => 'BUMDes Bukan Penugasan']);

        $this->actingAs($admin)
            ->get(route('rencana-aksi.index', ['bumdes_id' => $otherBumdes->id]))
            ->assertNotFound();
    }

    public function test_public_user_is_redirected_from_action_plan_endpoint(): void
    {
        $this->get(route('rencana-aksi.index'))->assertRedirect(route('login'));
    }

    public function test_action_plan_validation_rejects_missing_and_invalid_values(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)->post(route('bumdes.rencana-aksi.store', $bumdes), [
            'tahun' => 1999,
            'program' => '',
            'kegiatan' => '',
            'target_nilai' => '-1',
            'periode' => 'WEEKLY',
        ])->assertInvalid(['tahun', 'program', 'kegiatan', 'target_nilai', 'periode']);
    }

    public function test_year_filter_only_displays_selected_year(): void
    {
        [$admin, $bumdes] = $this->assigned();
        RencanaAksi::factory()->for($bumdes, 'bumdes')->create(['tahun' => 2025, 'program' => 'Program 2025']);
        RencanaAksi::factory()->for($bumdes, 'bumdes')->create(['tahun' => 2026, 'program' => 'Program 2026']);

        $this->actingAs($admin)
            ->get(route('rencana-aksi.index', ['tahun' => 2025]))
            ->assertOk()
            ->assertSeeText('Program 2025')
            ->assertDontSeeText('Program 2026');
    }

    public function test_action_period_must_be_a_supported_enum_value(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)
            ->post(route('bumdes.rencana-aksi.store', $bumdes), $this->payload(['periode' => 'SEMESTER']))
            ->assertInvalid(['periode']);
    }

    public function test_admin_kawasan_can_read_assigned_plan_but_cannot_create_one(): void
    {
        $admin = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $desa = Desa::factory()->create();
        $bumdes = Bumdes::factory()->create();
        $kawasan->adminUsers()->attach($admin);
        $kawasan->desas()->attach($desa);
        $bumdes->desas()->attach($desa, ['is_desa_utama' => true]);
        $plan = RencanaAksi::factory()->for($bumdes, 'bumdes')->create(['tahun' => now()->year]);

        $this->actingAs($admin)
            ->get(route('rencana-aksi.index', ['bumdes_id' => $bumdes->id]))
            ->assertOk()
            ->assertSeeText('Mode pemantauan')
            ->assertSeeText('Lihat realisasi')
            ->assertDontSeeText('Simpan & lanjut input realisasi');
        $this->actingAs($admin)->get(route('bumdes.rencana-aksi.show', [$bumdes, $plan]))->assertOk();
        $this->actingAs($admin)->post(route('bumdes.rencana-aksi.store', $bumdes), $this->payload())->assertForbidden();
    }

    public function test_action_plan_child_id_from_another_bumdes_returns_not_found(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $otherPlan = RencanaAksi::factory()->create();

        $this->actingAs($admin)
            ->get(route('bumdes.rencana-aksi.edit', [$bumdes, $otherPlan]))
            ->assertNotFound();
    }

    public function test_action_plan_update_is_logged(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $plan = RencanaAksi::factory()->for($bumdes, 'bumdes')->create();

        $this->actingAs($admin)
            ->put(route('bumdes.rencana-aksi.update', [$bumdes, $plan]), $this->payload(['program' => 'Program diperbarui']))
            ->assertRedirect();

        $this->assertDatabaseHas(RencanaAksi::class, ['id' => $plan->id, 'program' => 'Program diperbarui']);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'action-plan.updated', 'subject_id' => $plan->id]);
    }

    public function test_empty_action_plan_can_be_deleted_and_is_logged(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $plan = RencanaAksi::factory()->for($bumdes, 'bumdes')->create();

        $this->actingAs($admin)
            ->delete(route('bumdes.rencana-aksi.destroy', [$bumdes, $plan]))
            ->assertRedirect();

        $this->assertDatabaseMissing(RencanaAksi::class, ['id' => $plan->id]);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'action-plan.deleted', 'subject_id' => $plan->id]);
    }

    public function test_action_plan_with_realizations_cannot_be_deleted(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $plan = RencanaAksi::factory()->for($bumdes, 'bumdes')->create();
        RealisasiAksi::factory()->for($plan, 'actionPlan')->create();

        $this->actingAs($admin)
            ->from(route('bumdes.rencana-aksi.show', [$bumdes, $plan]))
            ->delete(route('bumdes.rencana-aksi.destroy', [$bumdes, $plan]))
            ->assertRedirect()
            ->assertSessionHasErrors('rencana_aksi');

        $this->assertDatabaseHas(RencanaAksi::class, ['id' => $plan->id]);
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'tahun' => now()->year,
            'program' => 'Penguatan pemasaran desa',
            'kegiatan' => 'Membuka kanal distribusi baru',
            'target_deskripsi' => 'Produk masuk ke pasar kecamatan',
            'target_nilai' => '100.00',
            'target_satuan' => 'unit',
            'periode' => ActionPeriod::Monthly->value,
            'tanggal_mulai' => now()->startOfYear()->format('Y-m-d'),
            'tanggal_selesai' => now()->endOfYear()->format('Y-m-d'),
        ], $overrides);
    }

    /** @return array{User, Bumdes} */
    private function assigned(): array
    {
        $admin = User::factory()->adminDesa()->create();
        $desa = Desa::factory()->create();
        $admin->desas()->attach($desa);
        $bumdes = Bumdes::factory()->create();
        $bumdes->desas()->attach($desa, ['is_desa_utama' => true]);

        return [$admin, $bumdes];
    }
}
