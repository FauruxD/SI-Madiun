<?php

namespace Tests\Feature;

use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class KawasanPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_manage_kawasan(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kawasan = KawasanPerdesaan::factory()->create();

        $this->assertTrue(Gate::forUser($superAdmin)->allows('viewAny', KawasanPerdesaan::class));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('create', KawasanPerdesaan::class));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('update', $kawasan));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('delete', $kawasan));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('manageDocuments', $kawasan));
    }

    public function test_admin_kawasan_has_read_only_access_to_assigned_kawasan(): void
    {
        $admin = User::factory()->adminKawasan()->create();
        $assigned = KawasanPerdesaan::factory()->create();
        $other = KawasanPerdesaan::factory()->create();
        $assigned->adminUsers()->attach($admin);

        $this->assertTrue(Gate::forUser($admin)->allows('view', $assigned));
        $this->assertFalse(Gate::forUser($admin)->allows('view', $other));
        $this->assertFalse(Gate::forUser($admin)->allows('update', $assigned));
        $this->assertFalse(Gate::forUser($admin)->allows('manageDocuments', $assigned));
    }

    public function test_admin_desa_cannot_view_or_manage_kawasan(): void
    {
        $adminDesa = User::factory()->adminDesa()->create();
        $kawasan = KawasanPerdesaan::factory()->create();

        $this->assertFalse(Gate::forUser($adminDesa)->allows('view', $kawasan));
        $this->assertFalse(Gate::forUser($adminDesa)->allows('create', KawasanPerdesaan::class));
        $this->assertFalse(Gate::forUser($adminDesa)->allows('update', $kawasan));
    }
}
