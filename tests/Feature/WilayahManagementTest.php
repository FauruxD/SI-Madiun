<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WilayahManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_create_complete_wilayah_hierarchy(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post(route('admin.kabupaten.store'), [
            'kode' => '35.19', 'nama' => 'Kabupaten Madiun',
        ])->assertRedirectToRoute('admin.kabupaten.index');
        $kabupaten = Kabupaten::query()->firstOrFail();

        $this->actingAs($superAdmin)->post(route('admin.kecamatan.store'), [
            'kabupaten_id' => $kabupaten->id, 'kode' => '35.19.13', 'nama' => 'Wungu',
        ])->assertRedirectToRoute('admin.kecamatan.index');
        $kecamatan = Kecamatan::query()->firstOrFail();

        $this->actingAs($superAdmin)->post(route('admin.desa.store'), [
            'kecamatan_id' => $kecamatan->id, 'kode' => '35.19.13.2005', 'nama' => 'Brumbun',
        ])->assertRedirectToRoute('admin.desa.index');

        $this->assertDatabaseHas(Desa::class, ['kode' => '35.19.13.2005']);
        $this->assertDatabaseCount(ActivityLog::class, 3);
    }

    public function test_parent_wilayah_with_children_cannot_be_deleted(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kecamatan = Kecamatan::factory()->create();

        $this->actingAs($superAdmin)->delete(route('admin.kabupaten.destroy', $kecamatan->kabupaten))
            ->assertSessionHasErrors(['kabupaten']);

        $this->assertDatabaseHas(Kabupaten::class, ['id' => $kecamatan->kabupaten_id]);
    }

    public function test_admin_desa_cannot_modify_master_wilayah(): void
    {
        $adminDesa = User::factory()->adminDesa()->create();

        $this->actingAs($adminDesa)->post(route('admin.kabupaten.store'), [
            'kode' => '35.20', 'nama' => 'Kabupaten Lain',
        ])->assertForbidden();
    }
}
