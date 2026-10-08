<?php

namespace Tests\Feature;

use App\Actions\RecordActivity;
use App\Enums\PotensiStatus;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\TitikInformasi;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class MapGeometryTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const POINT = ['type' => 'Point', 'coordinates' => [111.52, -7.63]];

    private const POLYGON = ['type' => 'Polygon', 'coordinates' => [[[111.5, -7.6], [111.6, -7.6], [111.6, -7.7], [111.5, -7.6]]]];

    #[TestWith(['superAdmin', 'kawasan'])]
    #[TestWith(['superAdmin', 'bumdes'])]
    #[TestWith(['superAdmin', 'potensi'])]
    #[TestWith(['adminKawasan', 'kawasan'])]
    #[TestWith(['adminKawasan', 'bumdes'])]
    #[TestWith(['adminKawasan', 'potensi'])]
    #[TestWith(['adminDesa', 'bumdes'])]
    #[TestWith(['adminDesa', 'potensi'])]
    public function test_assigned_actor_can_edit_only_geometry_with_audit(string $role, string $kind): void
    {
        [$actor, $entities] = $this->assigned($role);
        $entity = $entities[$kind];
        $before = $entity->getRawOriginal('status');
        $geometry = $kind === 'kawasan' ? self::POLYGON : self::POINT;

        $this->actingAs($actor)->get(route('peta.geometry.edit', [$kind, $entity->id]))->assertOk()->assertSee('data-map-editor', false);
        $this->put(route('peta.geometry.update', [$kind, $entity->id]), ['geometri' => json_encode($geometry)])->assertRedirect();

        $this->assertEquals($geometry, $entity->fresh()->geometri);
        $this->assertSame($before, $entity->fresh()->getRawOriginal('status'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'map.geometry.updated', 'subject_type' => $entity->getMorphClass(), 'subject_id' => $entity->id, 'user_id' => $actor->id]);
        if ($role === 'adminKawasan') {
            $this->assertFalse(Gate::forUser($actor)->allows('update', $entity));
        }
    }

    #[TestWith(['adminDesa', 'kawasan'])]
    #[TestWith(['adminKawasan', 'kawasan'])]
    #[TestWith(['adminKawasan', 'bumdes'])]
    #[TestWith(['adminKawasan', 'potensi'])]
    #[TestWith(['adminDesa', 'bumdes'])]
    #[TestWith(['adminDesa', 'potensi'])]
    public function test_out_of_scope_or_disallowed_role_cannot_discover_or_change_object(string $role, string $kind): void
    {
        [, $entities] = $this->assigned('superAdmin');
        $outsider = User::factory()->{$role}()->create();
        $entity = $entities[$kind];

        $this->actingAs($outsider)->get(route('peta.geometry.edit', [$kind, $entity->id]))->assertNotFound();
        $this->put(route('peta.geometry.update', [$kind, $entity->id]), ['geometri' => self::POINT])->assertNotFound();
        $this->assertNull($entity->fresh()->geometri);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'map.geometry.updated']);
    }

    public function test_admin_desa_does_not_gain_boundary_permission_even_when_village_belongs_to_kawasan(): void
    {
        [$actor, $entities] = $this->assigned('adminDesa');

        $this->assertFalse(Gate::forUser($actor)->allows('manageMap', $entities['kawasan']));
        $this->actingAs($actor)->put(route('peta.geometry.update', ['kawasan', $entities['kawasan']->id]), ['geometri' => self::POLYGON])->assertNotFound();
        $this->assertNull($entities['kawasan']->fresh()->geometri);
    }

    public function test_guest_can_read_public_map_but_cannot_open_or_write_editor(): void
    {
        [$actor, $entities] = $this->assigned('superAdmin');
        $this->get(route('peta.index'))->assertOk();
        $this->get(route('peta.manage'))->assertRedirect(route('login'));
        $this->get(route('peta.manage.data'))->assertRedirect(route('login'));
        $this->put(route('peta.geometry.update', ['bumdes', $entities['bumdes']->id]), ['geometri' => self::POINT])->assertRedirect(route('login'));
        $this->assertNull($entities['bumdes']->fresh()->geometri);
    }

    public function test_invalid_and_authority_payloads_do_not_mutate_geometry_or_publication(): void
    {
        [$actor, $entities] = $this->assigned('adminDesa');
        $potential = $entities['potensi'];
        $url = route('peta.geometry.update', ['potensi', $potential->id]);

        $this->actingAs($actor)->put($url, ['geometri' => ['type' => 'Point', 'coordinates' => [200, -7]]])->assertSessionHasErrors('geometri');
        $this->put($url, ['geometri' => self::POINT, 'status' => 'PUBLISHED', 'desa_id' => 999, 'is_active' => 1])->assertSessionHasErrors(['status', 'desa_id', 'is_active']);
        $this->put($url, [])->assertSessionHasErrors('geometri');

        $this->assertNull($potential->fresh()->geometri);
        $this->assertSame(PotensiStatus::Draft, $potential->fresh()->status);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'map.geometry.updated']);
    }

    public function test_explicit_null_clears_location_and_archived_object_remains_uneditable(): void
    {
        [$actor, $entities] = $this->assigned('superAdmin');
        $bumdes = $entities['bumdes'];
        $bumdes->geometri = self::POINT;
        $bumdes->save();

        $this->actingAs($actor)->put(route('peta.geometry.update', ['bumdes', $bumdes->id]), ['geometri' => null])->assertRedirect();
        $this->assertNull($bumdes->fresh()->geometri);
        $bumdes->delete();
        $this->put(route('peta.geometry.update', ['bumdes', $bumdes->id]), ['geometri' => self::POINT])->assertNotFound();
    }

    public function test_audit_failure_rolls_back_geometry_write(): void
    {
        [$actor, $entities] = $this->assigned('superAdmin');
        $this->mock(RecordActivity::class)->shouldReceive('execute')->once()->andThrow(new RuntimeException('audit unavailable'));
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($actor)->put(route('peta.geometry.update', ['bumdes', $entities['bumdes']->id]), ['geometri' => self::POINT]);
            $this->fail('Audit failure should propagate.');
        } catch (RuntimeException $exception) {
            $this->assertSame('audit unavailable', $exception->getMessage());
        }
        $this->assertNull($entities['bumdes']->fresh()->geometri);
    }

    public function test_information_point_create_update_deactivate_and_duplicate_guard(): void
    {
        [$actor, $entities] = $this->assigned('adminKawasan');
        $kawasan = $entities['kawasan'];
        $data = ['nama' => 'Balai pertemuan', 'jenis' => 'pertemuan', 'deskripsi' => 'Titik QA', 'geometri' => self::POINT, 'is_active' => 1];
        $this->actingAs($actor)->get(route('peta.information.create', $kawasan->id))->assertOk();
        $this->post(route('peta.information.store', $kawasan->id), $data)->assertRedirect();
        $point = $kawasan->informationPoints()->firstOrFail();
        $this->assertEquals(self::POINT, $point->geometri);
        $this->assertDatabaseHas('activity_logs', ['action' => 'map.information.created', 'subject_id' => $point->id]);

        $this->post(route('peta.information.store', $kawasan->id), $data)->assertSessionHasErrors('nama');
        $this->assertDatabaseCount('titik_informasi', 1);
        $this->get(route('peta.information.edit', [$kawasan->id, $point->id]))->assertOk();
        $this->put(route('peta.information.update', [$kawasan->id, $point->id]), [...$data, 'is_active' => 0])->assertRedirect();
        $this->assertFalse($point->fresh()->is_active);
        $this->assertDatabaseHas('activity_logs', ['action' => 'map.information.updated', 'subject_id' => $point->id]);
    }

    public function test_information_lookup_is_chained_to_assigned_parent_and_desa_cannot_create(): void
    {
        [$actor, $entities] = $this->assigned('adminKawasan');
        $foreign = TitikInformasi::factory()->create();
        $own = $entities['kawasan'];
        $data = ['nama' => 'Foreign mutation', 'jenis' => 'parkir', 'geometri' => self::POINT, 'is_active' => 1];

        $this->actingAs($actor)->get(route('peta.information.edit', [$own->id, $foreign->id]))->assertNotFound();
        $this->put(route('peta.information.update', [$own->id, $foreign->id]), $data)->assertNotFound();
        $this->post(route('peta.information.store', $foreign->kawasan_id), $data)->assertNotFound();
        [$desaAdmin] = $this->assigned('adminDesa');
        $this->actingAs($desaAdmin)->post(route('peta.information.store', $own->id), $data)->assertNotFound();
        $this->assertNotSame('Foreign mutation', $foreign->fresh()->nama);
        $this->assertDatabaseCount('titik_informasi', 1);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'map.information.updated']);
    }

    public function test_information_requires_valid_point_and_does_not_accept_parent_override(): void
    {
        [$actor, $entities] = $this->assigned('superAdmin');
        $this->actingAs($actor)->post(route('peta.information.store', $entities['kawasan']->id), [
            'nama' => 'Invalid', 'jenis' => 'untrusted', 'geometri' => self::POLYGON, 'is_active' => 1, 'kawasan_id' => 999,
        ])->assertSessionHasErrors(['jenis', 'geometri', 'kawasan_id']);
        $this->assertDatabaseCount('titik_informasi', 0);
    }

    public function test_internal_map_and_manage_lists_do_not_leak_other_assignments(): void
    {
        [$actor, $entities] = $this->assigned('adminDesa');
        $entities['bumdes']->geometri = self::POINT;
        $entities['bumdes']->save();
        $outside = Bumdes::factory()->create(['geometri' => self::POINT, 'nama' => 'Outside map office']);

        $this->actingAs($actor)->get(route('peta.manage'))->assertOk()->assertSee($entities['bumdes']->nama)->assertDontSee($outside->nama);
        $this->getJson(route('peta.manage.data'))->assertOk()->assertJsonCount(1, 'features')->assertJsonPath('features.0.id', 'bumdes-'.$entities['bumdes']->id);
        $this->get('/peta/kelola/unknown/1')->assertNotFound();
    }

    public function test_information_malformed_name_does_not_reach_unique_query_or_write(): void
    {
        [$actor, $entities] = $this->assigned('superAdmin');

        $this->actingAs($actor)->post(route('peta.information.store', $entities['kawasan']->id), [
            'nama' => ['unexpected'], 'jenis' => ['parkir'], 'geometri' => self::POINT, 'is_active' => 1,
        ])->assertSessionHasErrors(['nama', 'jenis']);

        $this->assertDatabaseCount('titik_informasi', 0);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'map.information.created']);
    }

    /** @return array{User, array<string, KawasanPerdesaan|Bumdes|PotensiDesa>} */
    private function assigned(string $role): array
    {
        $actor = User::factory()->{$role}()->create();
        $desa = Desa::factory()->create();
        $actor->desas()->attach($desa);
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->adminUsers()->attach($actor);
        $kawasan->desas()->attach($desa);
        $bumdes = Bumdes::factory()->create();
        $bumdes->desas()->attach($desa);
        $potential = PotensiDesa::factory()->create(['desa_id' => $desa->id]);

        return [$actor, ['kawasan' => $kawasan, 'bumdes' => $bumdes, 'potensi' => $potential]];
    }
}
