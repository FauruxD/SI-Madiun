<?php

namespace Tests\Feature;

use App\Enums\StatusKemitraan;
use App\Models\ActivityLog;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KemitraanBumdes;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class KemitraanBumdesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authorized_admin_can_create_running_partnership(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)->post(route('bumdes.kemitraan.store', $bumdes), [
            'nama_mitra' => 'PT Mitra Pangan', 'jenis_kemitraan' => 'Distribusi', 'deskripsi' => 'Distribusi produk desa.', 'status' => StatusKemitraan::Berjalan->value, 'kebutuhan_investasi' => '',
        ])->assertRedirect();

        $partnership = KemitraanBumdes::query()->firstOrFail();
        $this->assertNull($partnership->kebutuhan_investasi);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.partnership.created', 'subject_id' => $partnership->id]);
    }

    public function test_new_partnership_need_allows_nullable_partner_name(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)->post(route('bumdes.kemitraan.store', $bumdes), [
            'nama_mitra' => '', 'jenis_kemitraan' => 'Permodalan', 'deskripsi' => 'Mencari mitra ekspansi.', 'status' => StatusKemitraan::Dibutuhkan->value, 'kebutuhan_investasi' => '250000000.00',
        ])->assertRedirect();

        $this->assertNull(KemitraanBumdes::query()->firstOrFail()->nama_mitra);
    }

    public function test_unauthorized_admin_cannot_update_partnership(): void
    {
        $unassigned = User::factory()->adminDesa()->create();
        $partnership = KemitraanBumdes::factory()->create();

        $this->actingAs($unassigned)->put(route('bumdes.kemitraan.update', [$partnership->bumdes_id, $partnership]), [
            'nama_mitra' => 'Tidak boleh', 'jenis_kemitraan' => 'Distribusi', 'status' => StatusKemitraan::Berjalan->value,
        ])->assertNotFound();
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
