<?php

namespace Tests\Feature;

use App\Enums\AchievementStatus;
use App\Enums\EProposalStatus;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\EProposal;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\ProdukLayanan;
use App\Models\RencanaAksi;
use App\Models\UnitUsaha;
use App\Models\User;
use App\Services\DashboardSummaryService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const POINT = ['type' => 'Point', 'coordinates' => [111.52, -7.63]];

    private const POLYGON = ['type' => 'Polygon', 'coordinates' => [[[111.5, -7.6], [111.6, -7.6], [111.6, -7.7], [111.5, -7.6]]]];

    public function test_admin_desa_dashboard_summarizes_only_assigned_villages(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $assignedVillage = Desa::factory()->create(['nama' => 'Desa Dashboard Sendiri']);
        $outsideVillage = Desa::factory()->create(['nama' => 'Desa Dashboard Luar']);
        $admin->desas()->attach($assignedVillage);

        $ownedBumdes = Bumdes::factory()->create([
            'nama' => 'BUMDes Tani Makmur',
            'nomor_registrasi' => null,
            'tahun_berdiri' => 2022,
            'status_hukum' => null,
            'alamat' => 'Jalan Desa 1',
            'telepon' => null,
            'email' => null,
            'deskripsi' => 'Usaha pangan desa.',
        ]);
        $outsideBumdes = Bumdes::factory()->create(['nama' => 'BUMDes Tidak Boleh Terlihat']);
        $ownedBumdes->desas()->attach($assignedVillage, ['is_desa_utama' => true]);
        $outsideBumdes->desas()->attach($outsideVillage, ['is_desa_utama' => true]);

        $unit = UnitUsaha::factory()->for($ownedBumdes, 'bumdes')->create();
        ProdukLayanan::factory()->for($unit, 'businessUnit')->create(['is_active' => true]);
        $plan = RencanaAksi::factory()->for($ownedBumdes, 'bumdes')->create(['tahun' => now()->year, 'target_nilai' => '100.00']);
        $plan->realizations()->create([
            'periode_ke' => 1,
            'realisasi_nilai' => '50.00',
            'persentase_capaian' => '50.00',
            'status' => AchievementStatus::InProgress,
            'tanggal_realisasi' => now(),
        ]);

        $ownedPotential = PotensiDesa::factory()->for($assignedVillage, 'village')->create(['bumdes_id' => $ownedBumdes->id]);
        $outsidePotential = PotensiDesa::factory()->for($outsideVillage, 'village')->create(['bumdes_id' => $outsideBumdes->id]);
        EProposal::factory()->for($ownedPotential, 'potential')->for($admin, 'submitter')->create();
        EProposal::factory()->published()->for($outsidePotential, 'potential')->create();

        $response = $this->actingAs($admin)->get(route('dashboard', ['tahun' => now()->year]));

        $response->assertOk()
            ->assertSee('BUMDes Tani Makmur')
            ->assertSee('3 dari 7 data inti terisi')
            ->assertSee('50,00%')
            ->assertDontSee('BUMDes Tidak Boleh Terlihat')
            ->assertDontSee('Desa Dashboard Luar')
            ->assertViewHas('proposalStatusCounts', fn (array $counts): bool => $counts[EProposalStatus::Diajukan->value] === 1
                && $counts[EProposalStatus::Published->value] === 0)
            ->assertViewHas('bumdesSummaries', fn (Collection $rows): bool => $rows->count() === 1
                && $rows->first()['active_product_count'] === 1
                && $rows->first()['achievement']['rata_rata_capaian'] === '50.00');
    }

    public function test_admin_kawasan_dashboard_is_scoped_and_contains_map_proposals_and_achievement(): void
    {
        $admin = User::factory()->adminKawasan()->create();
        $assignedRegion = KawasanPerdesaan::factory()->ditetapkan()->create([
            'nama' => 'Kawasan Lereng Wilis',
            'geometri' => self::POLYGON,
        ]);
        $outsideRegion = KawasanPerdesaan::factory()->ditetapkan()->create([
            'nama' => 'Kawasan Rahasia Luar',
            'geometri' => self::POLYGON,
        ]);
        $assignedVillage = Desa::factory()->create(['nama' => 'Desa Dalam Kawasan']);
        $outsideVillage = Desa::factory()->create(['nama' => 'Desa Luar Kawasan']);
        $assignedRegion->adminUsers()->attach($admin);
        $assignedRegion->desas()->attach($assignedVillage);
        $outsideRegion->desas()->attach($outsideVillage);

        $insideBumdes = Bumdes::factory()->create(['nama' => 'BUMDes Kawasan Sendiri', 'geometri' => self::POINT]);
        $outsideBumdes = Bumdes::factory()->create(['nama' => 'BUMDes Kawasan Luar', 'geometri' => self::POINT]);
        $insideBumdes->desas()->attach($assignedVillage, ['is_desa_utama' => true]);
        $outsideBumdes->desas()->attach($outsideVillage, ['is_desa_utama' => true]);

        $plan = RencanaAksi::factory()->for($insideBumdes, 'bumdes')->create(['tahun' => now()->year, 'target_nilai' => '100.00']);
        $plan->realizations()->create([
            'periode_ke' => 1,
            'realisasi_nilai' => '75.00',
            'persentase_capaian' => '75.00',
            'status' => AchievementStatus::InProgress,
            'tanggal_realisasi' => now(),
        ]);

        $insidePotential = PotensiDesa::factory()->for($assignedVillage, 'village')->create(['bumdes_id' => $insideBumdes->id]);
        $outsidePotential = PotensiDesa::factory()->for($outsideVillage, 'village')->create(['bumdes_id' => $outsideBumdes->id]);
        EProposal::factory()->for($insidePotential, 'potential')->create();
        EProposal::factory()->published()->for($outsidePotential, 'potential')->create();

        $response = $this->actingAs($admin)->get(route('dashboard', ['tahun' => now()->year]));

        $response->assertOk()
            ->assertSee('Kawasan Lereng Wilis')
            ->assertSee('BUMDes Kawasan Sendiri')
            ->assertSee('75,00%')
            ->assertDontSee('Kawasan Rahasia Luar')
            ->assertDontSee('BUMDes Kawasan Luar')
            ->assertViewHas('kawasanSummaries', fn (Collection $rows): bool => $rows->count() === 1
                && $rows->first()['village_count'] === 1
                && $rows->first()['bumdes_count'] === 1
                && $rows->first()['proposal_counts'][EProposalStatus::Diajukan->value] === 1
                && $rows->first()['achievement']['rata_rata_capaian'] === '75.00')
            ->assertViewHas('mapCollection', fn (array $collection): bool => collect($collection['features'])->contains(
                fn (array $feature): bool => $feature['properties']['name'] === 'Kawasan Lereng Wilis',
            ) && ! collect($collection['features'])->contains(
                fn (array $feature): bool => $feature['properties']['name'] === 'Kawasan Rahasia Luar',
            ));
    }

    public function test_super_admin_dashboard_reports_kabupaten_totals_and_per_region_recap(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $firstRegion = KawasanPerdesaan::factory()->ditetapkan()->create(['nama' => 'Kawasan Rekap Satu']);
        $secondRegion = KawasanPerdesaan::factory()->create(['nama' => 'Kawasan Rekap Dua']);
        $firstVillage = Desa::factory()->create();
        $secondVillage = Desa::factory()->create();
        $firstRegion->desas()->attach($firstVillage);
        $secondRegion->desas()->attach($secondVillage);

        $firstBumdes = Bumdes::factory()->create();
        $secondBumdes = Bumdes::factory()->bumdesma()->create();
        $firstBumdes->desas()->attach($firstVillage, ['is_desa_utama' => true]);
        $secondBumdes->desas()->attach($secondVillage, ['is_desa_utama' => true]);

        $publishedPotential = PotensiDesa::factory()->published()->for($firstVillage, 'village')->create(['bumdes_id' => $firstBumdes->id]);
        $draftPotential = PotensiDesa::factory()->for($secondVillage, 'village')->create(['bumdes_id' => $secondBumdes->id]);
        EProposal::factory()->published()->for($publishedPotential, 'potential')->create();
        EProposal::factory()->for($draftPotential, 'potential')->create();

        $firstPlan = RencanaAksi::factory()->for($firstBumdes, 'bumdes')->create(['tahun' => now()->year, 'target_nilai' => '100.00']);
        $secondPlan = RencanaAksi::factory()->for($secondBumdes, 'bumdes')->create(['tahun' => now()->year, 'target_nilai' => '100.00']);
        foreach ([[$firstPlan, '40.00'], [$secondPlan, '80.00']] as [$plan, $percentage]) {
            $plan->realizations()->create([
                'periode_ke' => 1,
                'realisasi_nilai' => $percentage,
                'persentase_capaian' => $percentage,
                'status' => AchievementStatus::InProgress,
                'tanggal_realisasi' => now(),
            ]);
        }

        $response = $this->actingAs($superAdmin)->get(route('dashboard', ['tahun' => now()->year]));

        $response->assertOk()
            ->assertSee('Ringkasan Kabupaten Madiun')
            ->assertSee('60,00%')
            ->assertSee('Kawasan Rekap Satu')
            ->assertSee('Kawasan Rekap Dua')
            ->assertViewHas('metrics', fn (array $metrics): bool => collect($metrics)->pluck('value', 'label')->all() === [
                'Kawasan' => 2,
                'Desa' => 2,
                'BUMDes/BUMDesma' => 2,
                'Potensi published' => 1,
                'Proposal published' => 1,
                'Rata-rata capaian' => '60.00%',
            ])
            ->assertViewHas('kawasanSummaries', fn (Collection $rows): bool => $rows->count() === 2
                && $rows->firstWhere('model.id', $firstRegion->id)['achievement']['rata_rata_capaian'] === '40.00'
                && $rows->firstWhere('model.id', $secondRegion->id)['achievement']['rata_rata_capaian'] === '80.00');
    }

    public function test_dashboard_rejects_an_invalid_reporting_year(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard', ['tahun' => 1999]))
            ->assertSessionHasErrors('tahun');
    }

    public function test_kawasan_summary_query_count_stays_bounded_as_regions_grow(): void
    {
        $admin = User::factory()->adminKawasan()->create();

        foreach (range(1, 6) as $sequence) {
            $region = KawasanPerdesaan::factory()->create(['nama' => "Kawasan Performa {$sequence}"]);
            $village = Desa::factory()->create(['nama' => "Desa Performa {$sequence}"]);
            $bumdes = Bumdes::factory()->create(['nama' => "BUMDes Performa {$sequence}"]);
            $region->adminUsers()->attach($admin);
            $region->desas()->attach($village);
            $bumdes->desas()->attach($village, ['is_desa_utama' => true]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $data = app(DashboardSummaryService::class)->forUser($admin, now()->year);
        $queryCount = count(DB::getQueryLog());

        DB::disableQueryLog();

        $this->assertCount(6, $data['kawasanSummaries']);
        $this->assertLessThanOrEqual(12, $queryCount);
    }
}
