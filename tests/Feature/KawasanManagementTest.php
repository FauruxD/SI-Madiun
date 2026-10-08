<?php

namespace Tests\Feature;

use App\Enums\KawasanStatus;
use App\Models\ActivityLog;
use App\Models\Desa;
use App\Models\KategoriKawasan;
use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class KawasanManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_view_kawasan_list(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kawasan = KawasanPerdesaan::factory()->create(['nama' => 'Kawasan Wilis']);

        $this->actingAs($superAdmin)
            ->get(route('admin.kawasan.index'))
            ->assertOk()
            ->assertSee($kawasan->nama);

        $this->actingAs($superAdmin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSee('Dokumen legalitas');
    }

    public function test_super_admin_can_create_kawasan_with_multiple_villages_categories_and_admins(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $desas = Desa::factory()->count(2)->create();
        $categories = KategoriKawasan::factory()->count(2)->create();
        $admins = User::factory()->adminKawasan()->count(2)->create();

        $this->actingAs($superAdmin)->post(route('admin.kawasan.store'), [
            'nama' => 'Kawasan Lereng Wilis',
            'deskripsi' => 'Kolaborasi desa pada lereng Wilis.',
            'tanggal_penetapan' => '2026-08-17',
            'status' => KawasanStatus::Ditetapkan->value,
            'desa_ids' => $desas->modelKeys(),
            'category_ids' => $categories->modelKeys(),
            'admin_user_ids' => $admins->modelKeys(),
        ])->assertRedirect();

        $kawasan = KawasanPerdesaan::query()->where('nama', 'Kawasan Lereng Wilis')->firstOrFail();
        $this->assertEqualsCanonicalizing($desas->modelKeys(), $kawasan->desas()->pluck('desa.id')->all());
        $this->assertEqualsCanonicalizing($categories->modelKeys(), $kawasan->categories()->pluck('kategori_kawasan.id')->all());
        $this->assertEqualsCanonicalizing($admins->modelKeys(), $kawasan->adminUsers()->pluck('users.id')->all());
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'kawasan.created', 'subject_id' => $kawasan->id]);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'kawasan.admins.synced', 'subject_id' => $kawasan->id]);
    }

    public function test_super_admin_can_update_kawasan_and_sync_relationships(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $oldDesa = Desa::factory()->create();
        $newDesas = Desa::factory()->count(2)->create();
        $oldCategory = KategoriKawasan::factory()->create();
        $newCategory = KategoriKawasan::factory()->create();
        $oldAdmin = User::factory()->adminKawasan()->create();
        $newAdmin = User::factory()->adminKawasan()->create();
        $kawasan->desas()->attach($oldDesa);
        $kawasan->categories()->attach($oldCategory);
        $kawasan->adminUsers()->attach($oldAdmin);

        $this->actingAs($superAdmin)->put(route('admin.kawasan.update', $kawasan), [
            'nama' => 'Kawasan Diperbarui',
            'deskripsi' => '',
            'tanggal_penetapan' => '',
            'status' => KawasanStatus::Draft->value,
            'desa_ids' => $newDesas->modelKeys(),
            'category_ids' => [$newCategory->id],
            'admin_user_ids' => [$newAdmin->id],
        ])->assertRedirectToRoute('admin.kawasan.show', $kawasan);

        $this->assertSame('Kawasan Diperbarui', $kawasan->fresh()->nama);
        $this->assertEqualsCanonicalizing($newDesas->modelKeys(), $kawasan->desas()->pluck('desa.id')->all());
        $this->assertSame([$newCategory->id], $kawasan->categories()->pluck('kategori_kawasan.id')->all());
        $this->assertSame([$newAdmin->id], $kawasan->adminUsers()->pluck('users.id')->all());
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'kawasan.updated', 'subject_id' => $kawasan->id]);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'kawasan.villages.synced', 'subject_id' => $kawasan->id]);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'kawasan.categories.synced', 'subject_id' => $kawasan->id]);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'kawasan.admins.synced', 'subject_id' => $kawasan->id]);
    }

    public function test_create_kawasan_requires_valid_core_data_and_relationships(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post(route('admin.kawasan.store'), [
            'nama' => '',
            'status' => 'INVALID',
            'desa_ids' => [],
            'category_ids' => [],
        ])->assertInvalid(['nama', 'status', 'desa_ids', 'category_ids']);

        $this->assertDatabaseCount(KawasanPerdesaan::class, 0);
    }

    public function test_duplicate_relationship_ids_are_rejected_by_validation(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $desa = Desa::factory()->create();
        $category = KategoriKawasan::factory()->create();

        $this->actingAs($superAdmin)->post(route('admin.kawasan.store'), [
            'nama' => 'Kawasan Duplikat',
            'status' => KawasanStatus::Draft->value,
            'desa_ids' => [$desa->id, $desa->id],
            'category_ids' => [$category->id, $category->id],
        ])->assertInvalid(['desa_ids.1', 'category_ids.1']);
    }

    public function test_database_prevents_duplicate_kawasan_desa_pair(): void
    {
        $kawasan = KawasanPerdesaan::factory()->create();
        $desa = Desa::factory()->create();
        $kawasan->desas()->attach($desa);

        $this->expectException(QueryException::class);
        $kawasan->desas()->attach($desa);
    }

    public function test_database_prevents_duplicate_kawasan_category_pair(): void
    {
        $kawasan = KawasanPerdesaan::factory()->create();
        $category = KategoriKawasan::factory()->create();
        $kawasan->categories()->attach($category);

        $this->expectException(QueryException::class);
        $kawasan->categories()->attach($category);
    }

    public function test_deleting_kawasan_is_soft_and_does_not_delete_village_master(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $desa = Desa::factory()->create();
        $kawasan->desas()->attach($desa);

        $this->actingAs($superAdmin)
            ->delete(route('admin.kawasan.destroy', $kawasan))
            ->assertRedirectToRoute('admin.kawasan.index');

        $this->assertSoftDeleted($kawasan);
        $this->assertDatabaseHas(Desa::class, ['id' => $desa->id]);
    }

    public function test_village_picker_searches_in_database_and_preserves_selected_items(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $matching = Desa::factory()->create(['nama' => 'Banjarsari']);
        $selected = Desa::factory()->create(['nama' => 'Pilihan Lama']);
        Desa::factory()->create(['nama' => 'Tidak Cocok']);

        $this->actingAs($superAdmin)
            ->getJson(route('admin.kawasan.options.desa', [
                'search' => 'Banjar',
                'selected' => [$selected->id],
            ]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $matching->id, 'nama' => 'Banjarsari'])
            ->assertJsonFragment(['id' => $selected->id, 'nama' => 'Pilihan Lama']);
    }
}
