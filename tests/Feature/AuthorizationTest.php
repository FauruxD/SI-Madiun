<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirectToRoute('login');
    }

    public function test_each_role_gets_its_role_aware_dashboard(): void
    {
        $users = [
            ['user' => User::factory()->superAdmin()->create(), 'label' => 'Super Admin'],
            ['user' => User::factory()->adminKawasan()->create(), 'label' => 'Admin Kawasan'],
            ['user' => User::factory()->adminDesa()->create(), 'label' => 'Admin Desa'],
        ];

        foreach ($users as $item) {
            $this->actingAs($item['user'])->get(route('dashboard'))->assertOk()->assertSee($item['label']);
        }
    }

    public function test_non_super_admin_cannot_access_account_management(): void
    {
        $adminDesa = User::factory()->adminDesa()->create();
        $adminKawasan = User::factory()->adminKawasan()->create();

        $this->actingAs($adminDesa)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($adminKawasan)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_admin_desa_can_only_open_assigned_desa(): void
    {
        $adminDesa = User::factory()->adminDesa()->create();
        $assigned = Desa::factory()->create();
        $other = Desa::factory()->create();
        $adminDesa->desas()->attach($assigned);

        $this->actingAs($adminDesa)->get(route('desa.show', $assigned))->assertOk()->assertSee($assigned->nama);
        $this->actingAs($adminDesa)->get(route('desa.show', $other))->assertNotFound();
    }

    public function test_super_admin_can_open_any_desa(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $desa = Desa::factory()->create();

        $this->actingAs($superAdmin)->get(route('desa.show', $desa))->assertOk();
    }
}
