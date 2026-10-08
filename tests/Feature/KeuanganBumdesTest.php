<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KeuanganBumdes;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class KeuanganBumdesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_annual_financial_summary_preserves_decimal_precision_and_records_activity(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)->post(route('bumdes.keuangan.store', $bumdes), $this->payload())->assertRedirect();

        $summary = KeuanganBumdes::query()->firstOrFail();
        $this->assertSame('100000000.11', $summary->modal_awal);
        $this->assertSame('345678901.23', $summary->omzet);
        $this->assertSame('45678901.27', $summary->shu);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.financial-summary.created', 'subject_id' => $summary->id]);
    }

    public function test_duplicate_year_for_same_bumdes_is_rejected(): void
    {
        [$admin, $bumdes] = $this->assigned();
        KeuanganBumdes::factory()->for($bumdes, 'bumdes')->create(['tahun' => 2025]);

        $this->actingAs($admin)->post(route('bumdes.keuangan.store', $bumdes), $this->payload())->assertInvalid(['tahun']);
    }

    public function test_same_year_is_allowed_for_different_bumdes(): void
    {
        [$admin, $bumdes] = $this->assigned();
        KeuanganBumdes::factory()->create(['tahun' => 2025]);

        $this->actingAs($admin)->post(route('bumdes.keuangan.store', $bumdes), $this->payload())->assertRedirect();

        $this->assertSame(2, KeuanganBumdes::query()->where('tahun', 2025)->count());
    }

    public function test_financial_summary_from_another_bumdes_cannot_be_updated(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $other = KeuanganBumdes::factory()->create();

        $this->actingAs($admin)->put(route('bumdes.keuangan.update', [$bumdes, $other]), $this->payload())->assertNotFound();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return ['tahun' => 2025, 'modal_awal' => '100000000.11', 'omzet' => '345678901.23', 'shu' => '45678901.27', 'kontribusi_pades' => '7654321.19'];
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
