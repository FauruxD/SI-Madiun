<?php

namespace Tests\Feature;

use App\Enums\StatusUnitUsaha;
use App\Models\ActivityLog;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\ProdukLayanan;
use App\Models\UnitUsaha;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UnitUsahaProdukTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authorized_admin_can_create_and_deactivate_business_unit(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)->post(route('bumdes.unit-usaha.store', $bumdes), [
            'nama' => 'Perdagangan Pangan', 'deskripsi' => 'Pemasaran hasil tani.', 'status' => StatusUnitUsaha::Aktif->value,
        ])->assertRedirect();

        $unit = UnitUsaha::query()->firstOrFail();
        $this->actingAs($admin)->put(route('bumdes.unit-usaha.update', [$bumdes, $unit]), [
            'nama' => $unit->nama, 'deskripsi' => $unit->deskripsi, 'status' => StatusUnitUsaha::Nonaktif->value,
        ])->assertRedirect();

        $this->assertSame(StatusUnitUsaha::Nonaktif, $unit->fresh()->status);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.business-unit.created', 'subject_id' => $unit->id]);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.business-unit.updated', 'subject_id' => $unit->id]);
    }

    public function test_product_can_be_created_in_business_unit_with_exact_decimal_price(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $unit = UnitUsaha::factory()->for($bumdes, 'bumdes')->create();

        $this->actingAs($admin)->post(route('bumdes.produk.store', [$bumdes, $unit]), [
            'nama' => 'Beras Organik', 'kategori' => 'Pangan', 'deskripsi' => 'Beras hasil petani desa.', 'harga' => '12500.25', 'satuan' => 'kg', 'is_active' => true,
        ])->assertRedirect();

        $product = ProdukLayanan::query()->firstOrFail();
        $this->assertSame($unit->id, $product->unit_usaha_id);
        $this->assertSame('12500.25', $product->harga);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.product.created', 'subject_id' => $product->id]);
    }

    public function test_product_cannot_be_assigned_through_business_unit_from_another_bumdes(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $otherUnit = UnitUsaha::factory()->create();

        $this->actingAs($admin)->post(route('bumdes.produk.store', [$bumdes, $otherUnit]), [
            'nama' => 'Manipulasi', 'deskripsi' => 'Tidak boleh tersimpan.', 'is_active' => true,
        ])->assertNotFound();

        $this->assertDatabaseCount(ProdukLayanan::class, 0);
    }

    public function test_product_from_another_unit_cannot_be_modified_by_url_manipulation(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $unit = UnitUsaha::factory()->for($bumdes, 'bumdes')->create();
        $otherProduct = ProdukLayanan::factory()->create();

        $this->actingAs($admin)->put(route('bumdes.produk.update', [$bumdes, $unit, $otherProduct]), [
            'nama' => 'Manipulasi', 'deskripsi' => 'Tidak boleh.', 'is_active' => true,
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
