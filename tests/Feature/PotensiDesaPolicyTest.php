<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PotensiDesaPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_manage_every_potential(): void
    {
        $user = User::factory()->superAdmin()->create();
        $potential = PotensiDesa::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', PotensiDesa::class));
        $this->assertTrue(Gate::forUser($user)->allows('create', PotensiDesa::class));
        $this->assertTrue(Gate::forUser($user)->allows('update', $potential));
        $this->assertTrue(Gate::forUser($user)->allows('delete', $potential));
    }

    public function test_admin_desa_can_manage_only_assigned_village_potential(): void
    {
        $user = User::factory()->adminDesa()->create();
        $village = Desa::factory()->create();
        $user->desas()->attach($village);
        $inside = PotensiDesa::factory()->create(['desa_id' => $village->id]);
        $outside = PotensiDesa::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('view', $inside));
        $this->assertTrue(Gate::forUser($user)->allows('update', $inside));
        $this->assertFalse(Gate::forUser($user)->allows('view', $outside));
        $this->assertFalse(Gate::forUser($user)->allows('update', $outside));
    }

    public function test_admin_kawasan_has_read_only_access_to_assigned_kawasan_villages(): void
    {
        $user = User::factory()->adminKawasan()->create();
        $village = Desa::factory()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->desas()->attach($village);
        $kawasan->adminUsers()->attach($user);
        $potential = PotensiDesa::factory()->create(['desa_id' => $village->id]);

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', PotensiDesa::class));
        $this->assertTrue(Gate::forUser($user)->allows('view', $potential));
        $this->assertFalse(Gate::forUser($user)->allows('create', PotensiDesa::class));
        $this->assertFalse(Gate::forUser($user)->allows('update', $potential));
    }
}
