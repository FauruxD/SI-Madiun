<?php

namespace Tests\Feature;

use App\Enums\JenisBumdes;
use App\Enums\PotensiStatus;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KategoriPotensi;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PotensiDesaTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_assigned_admin_can_create_potential_for_own_village(): void
    {
        [$admin, $village, $category] = $this->assigned();

        $this->actingAs($admin)->post(route('potensi-desa.store'), $this->payload($village, $category))
            ->assertRedirect();

        $this->assertDatabaseHas('potensi_desa', ['desa_id' => $village->id, 'judul' => 'Kopi Brumbun']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'potential.created']);
    }

    public function test_admin_cannot_create_potential_for_another_village(): void
    {
        [$admin, , $category] = $this->assigned();
        $otherVillage = Desa::factory()->create();

        $this->actingAs($admin)->post(route('potensi-desa.store'), $this->payload($otherVillage, $category))
            ->assertNotFound();

        $this->assertDatabaseCount('potensi_desa', 0);
    }

    public function test_admin_cannot_update_potential_outside_own_scope(): void
    {
        [$admin, , $category] = $this->assigned();
        $potential = PotensiDesa::factory()->create(['kategori_potensi_id' => $category->id]);

        $this->actingAs($admin)->put(route('potensi-desa.update', $potential), $this->payload($potential->village, $category))
            ->assertNotFound();
    }

    public function test_required_potential_fields_have_indonesian_validation_messages(): void
    {
        [$admin] = $this->assigned();

        $this->actingAs($admin)->post(route('potensi-desa.store'), [])->assertInvalid([
            'desa_id' => 'Desa wajib dipilih.',
            'kategori_potensi_id' => 'Kategori potensi wajib dipilih.',
            'judul' => 'Judul potensi wajib diisi.',
            'deskripsi' => 'Deskripsi potensi wajib diisi.',
        ]);
    }

    public function test_super_admin_can_publish_potential_and_timestamp_is_recorded(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $village = Desa::factory()->create();
        $category = KategoriPotensi::factory()->create();

        $this->actingAs($admin)->post(route('potensi-desa.store'), [
            ...$this->payload($village, $category), 'status' => PotensiStatus::Published->value,
        ])->assertRedirect();

        $potential = PotensiDesa::query()->firstOrFail();
        $this->assertSame(PotensiStatus::Published, $potential->status);
        $this->assertNotNull($potential->published_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'potential.created']);
    }

    public function test_same_village_bumdes_can_be_linked(): void
    {
        [$admin, $village, $category] = $this->assigned();
        $bumdes = Bumdes::factory()->create(['jenis' => JenisBumdes::Bumdes]);
        $bumdes->desas()->attach($village, ['is_desa_utama' => true]);

        $this->actingAs($admin)->post(route('potensi-desa.store'), [
            ...$this->payload($village, $category), 'bumdes_id' => $bumdes->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('potensi_desa', ['bumdes_id' => $bumdes->id]);
    }

    public function test_bumdesma_covering_village_can_be_linked(): void
    {
        [$admin, $village, $category] = $this->assigned();
        $bumdesma = Bumdes::factory()->bumdesma()->create();
        $bumdesma->desas()->attach($village, ['is_desa_utama' => true]);
        $bumdesma->desas()->attach(Desa::factory()->create(), ['is_desa_utama' => false]);

        $this->actingAs($admin)->post(route('potensi-desa.store'), [
            ...$this->payload($village, $category), 'bumdes_id' => $bumdesma->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('potensi_desa', ['bumdes_id' => $bumdesma->id]);
    }

    public function test_bumdes_from_another_village_is_rejected(): void
    {
        [$admin, $village, $category] = $this->assigned();
        $bumdes = Bumdes::factory()->create();
        $bumdes->desas()->attach(Desa::factory()->create(), ['is_desa_utama' => true]);

        $this->actingAs($admin)->post(route('potensi-desa.store'), [
            ...$this->payload($village, $category), 'bumdes_id' => $bumdes->id,
        ])->assertInvalid(['bumdes_id' => 'BUMDes yang dipilih tidak terkait dengan desa ini.']);
    }

    public function test_bumdes_link_is_optional(): void
    {
        [$admin, $village, $category] = $this->assigned();

        $this->actingAs($admin)->post(route('potensi-desa.store'), $this->payload($village, $category))
            ->assertRedirect();

        $this->assertDatabaseHas('potensi_desa', ['bumdes_id' => null]);
    }

    public function test_inactive_category_cannot_be_selected_for_new_potential(): void
    {
        [$admin, $village] = $this->assigned();
        $category = KategoriPotensi::factory()->inactive()->create();

        $this->actingAs($admin)->post(route('potensi-desa.store'), $this->payload($village, $category))
            ->assertInvalid(['kategori_potensi_id' => 'Kategori potensi tidak aktif atau tidak tersedia.']);
    }

    public function test_super_admin_status_update_is_audited(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $potential = PotensiDesa::factory()->create();

        $this->actingAs($admin)->put(route('potensi-desa.update', $potential), [
            ...$this->payload($potential->village, $potential->category),
            'status' => PotensiStatus::Published->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('potensi_desa', ['id' => $potential->id, 'status' => PotensiStatus::Published->value]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'potential.status.changed', 'subject_id' => $potential->id]);
    }

    public function test_admin_kawasan_can_read_but_cannot_mutate_potential(): void
    {
        $kawasanAdmin = User::factory()->adminKawasan()->create();
        $village = Desa::factory()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->desas()->attach($village);
        $kawasan->adminUsers()->attach($kawasanAdmin);
        $potential = PotensiDesa::factory()->create(['desa_id' => $village->id]);

        $this->actingAs($kawasanAdmin)->get(route('potensi-desa.show', $potential))->assertOk();
        $this->actingAs($kawasanAdmin)->put(route('potensi-desa.update', $potential), [])->assertForbidden();
    }

    public function test_soft_deleted_potential_is_archived_and_logged(): void
    {
        [$admin, $village, $category] = $this->assigned();
        $potential = PotensiDesa::factory()->create(['desa_id' => $village->id, 'kategori_potensi_id' => $category->id]);

        $this->actingAs($admin)->delete(route('potensi-desa.destroy', $potential))->assertRedirectToRoute('potensi-desa.index');

        $this->assertSoftDeleted('potensi_desa', ['id' => $potential->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'potential.deleted', 'subject_id' => $potential->id]);
    }

    /** @return array{User, Desa, KategoriPotensi} */
    private function assigned(): array
    {
        $admin = User::factory()->adminDesa()->create();
        $village = Desa::factory()->create();
        $admin->desas()->attach($village);

        return [$admin, $village, KategoriPotensi::factory()->create()];
    }

    /** @return array<string, mixed> */
    private function payload(Desa $village, KategoriPotensi $category): array
    {
        return [
            'desa_id' => $village->id,
            'kategori_potensi_id' => $category->id,
            'judul' => 'Kopi Brumbun',
            'deskripsi' => 'Kopi lokal dengan kapasitas produksi dan cerita desa.',
            'data_pendukung' => 'Kebun siap dikembangkan bersama mitra.',
            'kebutuhan_investasi' => '250000000.00',
        ];
    }
}
