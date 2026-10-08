<?php

namespace Tests\Feature;

use App\Models\KategoriPotensi;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class KategoriPotensiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_create_category_with_normalized_unique_code(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.kategori-potensi.store'), [
            'nama' => 'Wisata Alam', 'kode' => ' wisata alam ', 'deskripsi' => 'Potensi wisata desa.', 'is_active' => true,
        ])->assertRedirectToRoute('admin.kategori-potensi.index');

        $this->assertDatabaseHas('kategori_potensi', ['nama' => 'Wisata Alam', 'kode' => 'WISATA_ALAM', 'is_active' => true]);
    }

    public function test_duplicate_category_code_is_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $category = KategoriPotensi::factory()->create(['kode' => 'PERTANIAN']);

        $this->actingAs($admin)->post(route('admin.kategori-potensi.store'), [
            'nama' => 'Duplikat', 'kode' => strtolower($category->kode), 'is_active' => true,
        ])->assertInvalid(['kode' => 'Kode kategori potensi sudah digunakan.']);
    }

    public function test_used_category_can_be_deactivated_without_breaking_potential(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $category = KategoriPotensi::factory()->create();
        $potential = PotensiDesa::factory()->create(['kategori_potensi_id' => $category->id]);

        $this->actingAs($admin)->put(route('admin.kategori-potensi.update', $category), [
            'nama' => $category->nama, 'kode' => $category->kode, 'is_active' => false,
        ])->assertRedirectToRoute('admin.kategori-potensi.index');

        $this->assertDatabaseHas('kategori_potensi', ['id' => $category->id, 'is_active' => false]);
        $this->assertDatabaseHas('potensi_desa', ['id' => $potential->id, 'kategori_potensi_id' => $category->id]);
    }

    public function test_non_super_admin_cannot_manage_category_master(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $this->actingAs($admin)->get(route('admin.kategori-potensi.index'))->assertForbidden();
    }
}
