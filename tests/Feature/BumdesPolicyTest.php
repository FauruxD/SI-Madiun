<?php

namespace Tests\Feature;

use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class BumdesPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_fully_manage_bumdes(): void
    {
        $user = User::factory()->superAdmin()->create();
        $bumdes = Bumdes::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Bumdes::class));
        $this->assertTrue(Gate::forUser($user)->allows('create', Bumdes::class));
        $this->assertTrue(Gate::forUser($user)->allows('view', $bumdes));
        $this->assertTrue(Gate::forUser($user)->allows('update', $bumdes));
        $this->assertTrue(Gate::forUser($user)->allows('delete', $bumdes));
        $this->assertTrue(Gate::forUser($user)->allows('manageData', $bumdes));
    }

    public function test_admin_desa_can_manage_only_bumdes_linked_to_assigned_village(): void
    {
        $user = User::factory()->adminDesa()->create();
        $assignedDesa = Desa::factory()->create();
        $user->desas()->attach($assignedDesa);
        $assigned = Bumdes::factory()->create();
        $other = Bumdes::factory()->create();
        $assigned->desas()->attach($assignedDesa, ['is_desa_utama' => true]);
        $other->desas()->attach(Desa::factory()->create(), ['is_desa_utama' => true]);

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Bumdes::class));
        $this->assertTrue(Gate::forUser($user)->allows('view', $assigned));
        $this->assertTrue(Gate::forUser($user)->allows('update', $assigned));
        $this->assertTrue(Gate::forUser($user)->allows('manageData', $assigned));
        $this->assertFalse(Gate::forUser($user)->allows('view', $other));
        $this->assertFalse(Gate::forUser($user)->allows('delete', $assigned));
    }

    public function test_admin_kawasan_can_only_read_bumdes_from_assigned_kawasan(): void
    {
        $user = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $desa = Desa::factory()->create();
        $kawasan->adminUsers()->attach($user);
        $kawasan->desas()->attach($desa);
        $assigned = Bumdes::factory()->create();
        $assigned->desas()->attach($desa, ['is_desa_utama' => true]);
        $other = Bumdes::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('view', $assigned));
        $this->assertFalse(Gate::forUser($user)->allows('view', $other));
        $this->assertFalse(Gate::forUser($user)->allows('update', $assigned));
        $this->assertFalse(Gate::forUser($user)->allows('manageData', $assigned));
    }
}
