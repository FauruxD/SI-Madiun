<?php

namespace Tests\Feature;

use App\Enums\JenisBumdes;
use App\Models\ActivityLog;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BumdesProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_create_bumdes_with_one_primary_village_and_activity_log(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $desa = Desa::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('bumdes.store'), $this->validPayload([$desa->id], $desa->id))
            ->assertRedirect();

        $bumdes = Bumdes::query()->where('nama', 'BUMDes Makmur Bersama')->firstOrFail();
        $this->assertSame(JenisBumdes::Bumdes, $bumdes->jenis);
        $this->assertSame([$desa->id], $bumdes->desas()->pluck('desa.id')->all());
        $this->assertTrue((bool) $bumdes->desas()->firstOrFail()->pivot->is_desa_utama);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.created', 'subject_id' => $bumdes->id]);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.villages.synced', 'subject_id' => $bumdes->id]);
    }

    public function test_super_admin_can_create_bumdesma_with_multiple_villages(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $desas = Desa::factory()->count(3)->create();
        $payload = $this->validPayload($desas->modelKeys(), $desas->last()->id);
        $payload['jenis'] = JenisBumdes::Bumdesma->value;

        $this->actingAs($superAdmin)->post(route('bumdes.store'), $payload)->assertRedirect();

        $bumdes = Bumdes::query()->firstOrFail();
        $this->assertEqualsCanonicalizing($desas->modelKeys(), $bumdes->desas()->pluck('desa.id')->all());
        $this->assertSame($desas->last()->id, $bumdes->desas()->wherePivot('is_desa_utama', true)->value('desa.id'));
    }

    public function test_regular_bumdes_rejects_more_than_one_village(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $desas = Desa::factory()->count(2)->create();

        $this->actingAs($superAdmin)
            ->post(route('bumdes.store'), $this->validPayload($desas->modelKeys(), $desas->first()->id))
            ->assertInvalid(['desa_ids']);

        $this->assertDatabaseCount(Bumdes::class, 0);
    }

    public function test_profile_validation_rejects_invalid_core_data_and_duplicate_registration(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Bumdes::factory()->create(['nomor_registrasi' => 'REG-001']);

        $this->actingAs($superAdmin)->post(route('bumdes.store'), [
            'nama' => '',
            'jenis' => 'INVALID',
            'nomor_registrasi' => 'REG-001',
            'tahun_berdiri' => ((int) date('Y')) + 1,
            'email' => 'tidak-valid',
            'desa_ids' => [],
        ])->assertInvalid(['nama', 'jenis', 'nomor_registrasi', 'tahun_berdiri', 'email', 'desa_ids', 'desa_utama_id']);
    }

    public function test_admin_desa_can_view_and_update_bumdes_in_assigned_village(): void
    {
        $adminDesa = User::factory()->adminDesa()->create();
        $desa = Desa::factory()->create();
        $adminDesa->desas()->attach($desa);
        $bumdes = Bumdes::factory()->create();
        $bumdes->desas()->attach($desa, ['is_desa_utama' => true]);

        $this->actingAs($adminDesa)
            ->get(route('bumdes.show', $bumdes))
            ->assertOk()
            ->assertSee($bumdes->nama);

        $this->actingAs($adminDesa)->put(route('bumdes.update', $bumdes), [
            'nama' => 'BUMDes Diperbarui Desa',
            'nomor_registrasi' => 'REG-DESA-01',
            'tahun_berdiri' => '2020',
            'status_hukum' => 'Perdes Nomor 2 Tahun 2020',
            'alamat' => 'Jalan Desa Nomor 1',
            'telepon' => '08123456789',
            'email' => 'bumdes@example.test',
            'deskripsi' => 'Profil yang diperbarui operator desa.',
        ])->assertRedirectToRoute('bumdes.show', $bumdes);

        $this->assertSame('BUMDes Diperbarui Desa', $bumdes->fresh()->nama);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.updated', 'subject_id' => $bumdes->id]);
    }

    public function test_admin_desa_cannot_view_or_update_bumdes_outside_assignment(): void
    {
        $adminDesa = User::factory()->adminDesa()->create();
        $adminDesa->desas()->attach(Desa::factory()->create());
        $otherBumdes = Bumdes::factory()->create();
        $otherBumdes->desas()->attach(Desa::factory()->create(), ['is_desa_utama' => true]);

        $this->actingAs($adminDesa)->get(route('bumdes.show', $otherBumdes))->assertNotFound();
        $this->actingAs($adminDesa)->put(route('bumdes.update', $otherBumdes), [
            'nama' => 'Tidak boleh',
        ])->assertNotFound();
    }

    public function test_admin_desa_cannot_change_village_ownership_or_bumdes_type(): void
    {
        $adminDesa = User::factory()->adminDesa()->create();
        $assigned = Desa::factory()->create();
        $unassigned = Desa::factory()->create();
        $adminDesa->desas()->attach($assigned);
        $bumdes = Bumdes::factory()->create(['jenis' => JenisBumdes::Bumdes]);
        $bumdes->desas()->attach($assigned, ['is_desa_utama' => true]);

        $this->actingAs($adminDesa)->put(route('bumdes.update', $bumdes), [
            'nama' => 'Nama Aman',
            'jenis' => JenisBumdes::Bumdesma->value,
            'desa_ids' => [$unassigned->id],
            'desa_utama_id' => $unassigned->id,
        ])->assertRedirect();

        $bumdes->refresh();
        $this->assertSame(JenisBumdes::Bumdes, $bumdes->jenis);
        $this->assertSame([$assigned->id], $bumdes->desas()->pluck('desa.id')->all());
    }

    public function test_admin_kawasan_has_read_only_access_to_bumdes_in_assigned_kawasan(): void
    {
        $adminKawasan = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $desa = Desa::factory()->create();
        $kawasan->adminUsers()->attach($adminKawasan);
        $kawasan->desas()->attach($desa);
        $bumdes = Bumdes::factory()->create();
        $bumdes->desas()->attach($desa, ['is_desa_utama' => true]);

        $this->actingAs($adminKawasan)->get(route('bumdes.show', $bumdes))->assertOk();
        $this->actingAs($adminKawasan)->put(route('bumdes.update', $bumdes), ['nama' => 'Ditolak'])->assertForbidden();
    }

    public function test_guest_cannot_access_bumdes_admin_endpoints(): void
    {
        $bumdes = Bumdes::factory()->create();

        $this->get(route('bumdes.index'))->assertRedirectToRoute('login');
        $this->get(route('bumdes.show', $bumdes))->assertRedirectToRoute('login');
        $this->post(route('bumdes.store'), [])->assertRedirectToRoute('login');
    }

    public function test_database_prevents_duplicate_bumdes_village_pair(): void
    {
        $bumdes = Bumdes::factory()->create();
        $desa = Desa::factory()->create();
        $bumdes->desas()->attach($desa, ['is_desa_utama' => true]);

        $this->expectException(QueryException::class);
        $bumdes->desas()->attach($desa, ['is_desa_utama' => false]);
    }

    public function test_syncing_villages_does_not_delete_village_master(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $bumdes = Bumdes::factory()->bumdesma()->create();
        $oldDesa = Desa::factory()->create();
        $newDesa = Desa::factory()->create();
        $bumdes->desas()->attach($oldDesa, ['is_desa_utama' => true]);
        $payload = $this->validPayload([$newDesa->id], $newDesa->id);
        $payload['jenis'] = JenisBumdes::Bumdesma->value;

        $this->actingAs($superAdmin)->put(route('bumdes.update', $bumdes), $payload)->assertRedirect();

        $this->assertDatabaseHas(Desa::class, ['id' => $oldDesa->id]);
        $this->assertSame([$newDesa->id], $bumdes->desas()->pluck('desa.id')->all());
    }

    public function test_list_search_and_filters_are_applied_in_database(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $desa = Desa::factory()->create();
        $matching = Bumdes::factory()->bumdesma()->create(['nama' => 'BUMDesma Target Wilis', 'status_hukum' => 'Perdes Bersama']);
        $matching->desas()->attach($desa, ['is_desa_utama' => true]);
        Bumdes::factory()->create(['nama' => 'BUMDes Lain']);

        $this->actingAs($superAdmin)->get(route('bumdes.index', [
            'search' => 'Target Wilis',
            'jenis' => JenisBumdes::Bumdesma->value,
            'desa' => $desa->id,
            'kecamatan' => $desa->kecamatan_id,
            'status' => 'Perdes Bersama',
        ]))->assertOk()->assertSee($matching->nama)->assertDontSee('BUMDes Lain');
    }

    public function test_free_text_is_escaped_on_the_detail_page(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $bumdes = Bumdes::factory()->create([
            'nama' => '<script>alert("xss")</script>',
            'deskripsi' => '<img src=x onerror=alert(1)>',
        ]);
        $bumdes->desas()->attach(Desa::factory()->create(), ['is_desa_utama' => true]);

        $this->actingAs($superAdmin)
            ->get(route('bumdes.show', $bumdes))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    /** @param non-empty-string $label */
    #[DataProvider('detailTabs')]
    public function test_all_detail_tabs_render_for_authorized_user(string $tab, string $label): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $bumdes = Bumdes::factory()->create();
        $bumdes->desas()->attach(Desa::factory()->create(), ['is_desa_utama' => true]);

        $this->actingAs($superAdmin)
            ->get(route('bumdes.show', ['bumdes' => $bumdes, 'tab' => $tab]))
            ->assertOk()
            ->assertSee($label, false);
    }

    /** @return array<string, array{string, string}> */
    public static function detailTabs(): array
    {
        return [
            'profile' => ['profil', 'Profil & legalitas'],
            'organization' => ['organisasi', 'Struktur organisasi'],
            'business units' => ['unit-usaha', 'Unit usaha'],
            'products' => ['produk', 'Produk & layanan'],
            'assets' => ['aset', 'Data aset tetap'],
            'finance' => ['keuangan', 'Ringkasan kinerja keuangan'],
            'partnerships' => ['kemitraan', 'Kemitraan & kebutuhan investasi'],
            'gallery' => ['galeri', 'Galeri BUMDes'],
            'documents' => ['dokumen', 'Dokumen AD/ART & legalitas'],
        ];
    }

    /** @param array<int, int> $desaIds */
    private function validPayload(array $desaIds, int $primaryVillageId): array
    {
        return [
            'nama' => 'BUMDes Makmur Bersama',
            'jenis' => JenisBumdes::Bumdes->value,
            'nomor_registrasi' => 'REG-2026-001',
            'tahun_berdiri' => '2020',
            'status_hukum' => 'Perdes pendirian',
            'alamat' => 'Desa Brumbun, Kabupaten Madiun',
            'telepon' => '08123456789',
            'email' => 'bumdes@example.test',
            'deskripsi' => 'Unit ekonomi desa.',
            'desa_ids' => $desaIds,
            'desa_utama_id' => $primaryVillageId,
        ];
    }
}
