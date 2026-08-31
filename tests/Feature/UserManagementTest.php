<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Desa;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_create_admin_desa_with_assignments(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $desas = Desa::factory()->count(2)->create();

        $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Admin Desa Baru',
            'username' => 'admin.baru',
            'email' => 'admin.baru@example.test',
            'phone' => '08123456789',
            'role' => UserRole::AdminDesa->value,
            'is_active' => true,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'desa_ids' => $desas->modelKeys(),
        ])->assertRedirectToRoute('admin.users.index');

        $created = User::query()->where('email', 'admin.baru@example.test')->firstOrFail();
        $this->assertEqualsCanonicalizing($desas->modelKeys(), $created->desas()->pluck('desa.id')->all());
        $this->assertDatabaseHas(ActivityLog::class, ['user_id' => $superAdmin->id, 'action' => 'user.created']);
    }

    public function test_admin_desa_requires_at_least_one_desa_assignment(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Tanpa Desa',
            'username' => 'tanpa.desa',
            'email' => 'tanpa@example.test',
            'role' => UserRole::AdminDesa->value,
            'is_active' => true,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertInvalid(['desa_ids']);
    }

    public function test_changing_role_removes_stale_desa_assignments(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $managedUser = User::factory()->adminDesa()->create();
        $managedUser->desas()->attach(Desa::factory()->create());

        $this->actingAs($superAdmin)->put(route('admin.users.update', $managedUser), [
            'name' => $managedUser->name,
            'username' => $managedUser->username,
            'email' => $managedUser->email,
            'phone' => '',
            'role' => UserRole::AdminKawasan->value,
            'is_active' => true,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirectToRoute('admin.users.index');

        $this->assertSame(UserRole::AdminKawasan, $managedUser->fresh()->role);
        $this->assertCount(0, $managedUser->fresh()->desas);
    }

    public function test_super_admin_cannot_demote_own_account(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->put(route('admin.users.update', $superAdmin), [
            'name' => $superAdmin->name,
            'username' => $superAdmin->username,
            'email' => $superAdmin->email,
            'role' => UserRole::AdminKawasan->value,
            'is_active' => true,
        ])->assertInvalid(['role']);

        $this->assertSame(UserRole::SuperAdmin, $superAdmin->fresh()->role);
    }
}
