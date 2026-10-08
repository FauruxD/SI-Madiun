<?php

namespace Tests\Feature;

use App\Enums\KawasanStatus;
use App\Models\Desa;
use App\Models\KategoriKawasan;
use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class KategoriKawasanManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_create_category_with_normalized_unique_code(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post(route('admin.kategori-kawasan.store'), [
            'nama' => 'Agrowisata',
            'kode' => ' agro wisata ',
            'deskripsi' => 'Kawasan berbasis wisata pertanian.',
            'is_active' => true,
        ])->assertRedirectToRoute('admin.kategori-kawasan.index');

        $this->assertDatabaseHas(KategoriKawasan::class, [
            'nama' => 'Agrowisata',
            'kode' => 'AGRO_WISATA',
            'is_active' => true,
        ]);
    }

    public function test_category_code_must_be_unique_when_created_or_updated(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $first = KategoriKawasan::factory()->create(['kode' => 'AGRO']);
        $second = KategoriKawasan::factory()->create(['kode' => 'MINA']);

        $this->actingAs($superAdmin)->post(route('admin.kategori-kawasan.store'), [
            'nama' => 'Duplikat',
            'kode' => strtolower($first->kode),
            'is_active' => true,
        ])->assertInvalid(['kode']);

        $this->actingAs($superAdmin)->put(route('admin.kategori-kawasan.update', $second), [
            'nama' => $second->nama,
            'kode' => $first->kode,
            'is_active' => true,
        ])->assertInvalid(['kode']);
    }

    public function test_super_admin_can_update_and_deactivate_category(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $category = KategoriKawasan::factory()->create();

        $this->actingAs($superAdmin)->put(route('admin.kategori-kawasan.update', $category), [
            'nama' => 'Nama Baru',
            'kode' => 'NAMA_BARU',
            'deskripsi' => '',
            'is_active' => false,
        ])->assertRedirectToRoute('admin.kategori-kawasan.index');

        $this->assertDatabaseHas(KategoriKawasan::class, [
            'id' => $category->id,
            'nama' => 'Nama Baru',
            'is_active' => false,
        ]);
    }

    public function test_deactivating_used_category_preserves_historical_relationship(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $category = KategoriKawasan::factory()->create();
        $kawasan->categories()->attach($category);

        $this->actingAs($superAdmin)->put(route('admin.kategori-kawasan.update', $category), [
            'nama' => $category->nama,
            'kode' => $category->kode,
            'is_active' => false,
        ])->assertRedirectToRoute('admin.kategori-kawasan.index');

        $this->assertDatabaseHas('kawasan_kategori', [
            'kawasan_id' => $kawasan->id,
            'kategori_kawasan_id' => $category->id,
        ]);
    }

    public function test_inactive_category_can_remain_on_its_existing_kawasan_but_cannot_be_added_elsewhere(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $desa = Desa::factory()->create();
        $historicalKawasan = KawasanPerdesaan::factory()->create();
        $otherKawasan = KawasanPerdesaan::factory()->create();
        $inactiveCategory = KategoriKawasan::factory()->inactive()->create();
        $historicalKawasan->categories()->attach($inactiveCategory);
        $historicalKawasan->desas()->attach($desa);
        $otherKawasan->desas()->attach($desa);

        $payload = [
            'nama' => 'Kawasan Historis',
            'status' => KawasanStatus::Draft->value,
            'desa_ids' => [$desa->id],
            'category_ids' => [$inactiveCategory->id],
        ];

        $this->actingAs($superAdmin)
            ->put(route('admin.kawasan.update', $historicalKawasan), $payload)
            ->assertRedirectToRoute('admin.kawasan.show', $historicalKawasan);

        $this->actingAs($superAdmin)
            ->put(route('admin.kawasan.update', $otherKawasan), $payload)
            ->assertInvalid(['category_ids.0']);

        $this->assertDatabaseHas('kawasan_kategori', [
            'kawasan_id' => $historicalKawasan->id,
            'kategori_kawasan_id' => $inactiveCategory->id,
        ]);
    }
}
