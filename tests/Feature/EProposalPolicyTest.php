<?php

namespace Tests\Feature;

use App\Enums\EProposalStatus;
use App\Models\Desa;
use App\Models\EProposal;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class EProposalPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_desa_can_view_and_resubmit_only_own_revision_proposal(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $village = Desa::factory()->create();
        $admin->desas()->attach($village);
        $own = EProposal::factory()->revisionRequired()->create(['potensi_desa_id' => PotensiDesa::factory()->create(['desa_id' => $village->id])]);
        $other = EProposal::factory()->revisionRequired()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('view', $own));
        $this->assertFalse(Gate::forUser($admin)->allows('reviewAny', EProposal::class));
        $this->assertFalse(Gate::forUser($admin)->allows('review', $own));
        $this->assertTrue(Gate::forUser($admin)->allows('resubmit', $own));
        $this->assertFalse(Gate::forUser($admin)->allows('view', $other));
        $this->assertFalse(Gate::forUser($admin)->allows('verify', $own));
        $this->assertFalse(Gate::forUser($admin)->allows('download', $own));
    }

    public function test_admin_kawasan_can_review_and_download_only_assigned_area(): void
    {
        $reviewer = User::factory()->adminKawasan()->create();
        $village = Desa::factory()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->desas()->attach($village);
        $kawasan->adminUsers()->attach($reviewer);
        $submitted = EProposal::factory()->create(['potensi_desa_id' => PotensiDesa::factory()->create(['desa_id' => $village->id])]);
        $verified = EProposal::factory()->verified()->create(['potensi_desa_id' => PotensiDesa::factory()->create(['desa_id' => $village->id])]);
        $other = EProposal::factory()->create();

        $this->assertTrue(Gate::forUser($reviewer)->allows('requestRevision', $submitted));
        $this->assertTrue(Gate::forUser($reviewer)->allows('reviewAny', EProposal::class));
        $this->assertTrue(Gate::forUser($reviewer)->allows('review', $submitted));
        $this->assertFalse(Gate::forUser($reviewer)->allows('review', $other));
        $this->assertTrue(Gate::forUser($reviewer)->allows('verify', $submitted));
        $this->assertTrue(Gate::forUser($reviewer)->allows('publish', $verified));
        $this->assertTrue(Gate::forUser($reviewer)->allows('download', $verified));
        $this->assertFalse(Gate::forUser($reviewer)->allows('view', $other));
        $this->assertFalse(Gate::forUser($reviewer)->allows('create', EProposal::class));
    }

    public function test_published_proposal_is_terminal_for_every_internal_role(): void
    {
        $proposal = EProposal::factory()->published()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->assertFalse(Gate::forUser($superAdmin)->allows('requestRevision', $proposal));
        $this->assertFalse(Gate::forUser($superAdmin)->allows('verify', $proposal));
        $this->assertFalse(Gate::forUser($superAdmin)->allows('publish', $proposal));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('download', $proposal));
        $this->assertSame(EProposalStatus::Published, $proposal->status);
    }
}
