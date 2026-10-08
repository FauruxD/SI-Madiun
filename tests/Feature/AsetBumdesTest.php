<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AsetBumdes;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AsetBumdesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authorized_admin_can_create_asset_with_exact_decimal_value(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)->post(route('bumdes.aset.store', $bumdes), $this->payload())->assertRedirect();

        $asset = AsetBumdes::query()->firstOrFail();
        $this->assertSame('987654321.09', $asset->nilai_perkiraan);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.asset.created', 'subject_id' => $asset->id]);
    }

    public function test_unauthorized_admin_cannot_update_asset(): void
    {
        $unassigned = User::factory()->adminDesa()->create();
        $asset = AsetBumdes::factory()->create();

        $this->actingAs($unassigned)->put(route('bumdes.aset.update', [$asset->bumdes_id, $asset]), $this->payload())->assertNotFound();
    }

    public function test_asset_from_another_bumdes_cannot_be_updated_with_child_id_manipulation(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $other = AsetBumdes::factory()->create();

        $this->actingAs($admin)->put(route('bumdes.aset.update', [$bumdes, $other]), $this->payload())->assertNotFound();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return ['nama' => 'Gedung produksi', 'jenis' => 'Bangunan', 'deskripsi' => 'Gedung pengolahan.', 'nilai_perkiraan' => '987654321.09', 'status_kepemilikan' => 'Milik sendiri', 'kondisi' => 'Baik'];
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
