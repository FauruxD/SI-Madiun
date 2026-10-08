<?php

namespace Tests\Feature;

use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class MapPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith(['superAdmin', true, true, true, true])]
    #[TestWith(['superAdmin', false, true, true, true])]
    #[TestWith(['adminKawasan', true, true, true, true])]
    #[TestWith(['adminKawasan', false, false, false, false])]
    #[TestWith(['adminDesa', true, false, true, true])]
    #[TestWith(['adminDesa', false, false, false, false])]
    public function test_geometry_permission_matrix_does_not_expand_profile_update(
        string $role, bool $assigned, bool $boundaryAllowed, bool $officeAllowed, bool $potentialAllowed,
    ): void {
        $actor = User::factory()->{$role}()->create();
        $desa = Desa::factory()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->desas()->attach($desa);
        $bumdes = Bumdes::factory()->create();
        $bumdes->desas()->attach($desa);
        $potential = PotensiDesa::factory()->create(['desa_id' => $desa->id]);
        if ($assigned) {
            $actor->desas()->attach($desa);
            $kawasan->adminUsers()->attach($actor);
        }

        $gate = Gate::forUser($actor);

        $this->assertSame($boundaryAllowed, $gate->allows('manageMap', $kawasan));
        $this->assertSame($officeAllowed, $gate->allows('manageMap', $bumdes));
        $this->assertSame($potentialAllowed, $gate->allows('manageMap', $potential));
        $this->assertSame($role === 'superAdmin', $gate->allows('update', $kawasan));
        $this->assertSame($role === 'superAdmin' || ($role === 'adminDesa' && $assigned), $gate->allows('update', $bumdes));
        $this->assertSame($role === 'superAdmin' || ($role === 'adminDesa' && $assigned), $gate->allows('update', $potential));
    }
}
