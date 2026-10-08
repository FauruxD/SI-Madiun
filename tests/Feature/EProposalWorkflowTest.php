<?php

namespace Tests\Feature;

use App\Actions\RecordActivity;
use App\Enums\EProposalStatus;
use App\Enums\PotensiStatus;
use App\Models\Desa;
use App\Models\EProposal;
use App\Models\KategoriPotensi;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\User;
use App\Notifications\EProposalStatusChanged;
use App\Services\EProposalWorkflowService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class EProposalWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_desa_can_submit_only_own_potential_and_duplicate_is_rejected(): void
    {
        [$admin, $potential] = $this->assignedPotential();
        $payload = ['potensi_desa_id' => $potential->id, 'catatan_pengaju' => 'Siap ditinjau.'];

        $this->actingAs($admin)->post(route('admin.eproposal.store'), $payload)->assertRedirect();
        $this->assertDatabaseHas('eproposal', ['potensi_desa_id' => $potential->id, 'submitted_by' => $admin->id, 'status' => EProposalStatus::Diajukan->value]);
        $this->assertNotNull(EProposal::query()->firstOrFail()->submitted_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'eproposal.submitted']);
        $this->actingAs($admin)->post(route('admin.eproposal.store'), $payload)->assertInvalid(['potensi_desa_id']);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_admin_desa_cannot_submit_other_village_potential(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $admin->desas()->attach(Desa::factory()->create());
        $potential = PotensiDesa::factory()->create();

        $this->actingAs($admin)->post(route('admin.eproposal.store'), ['potensi_desa_id' => $potential->id])->assertInvalid(['potensi_desa_id']);
        $this->assertDatabaseCount('eproposal', 0);
    }

    public function test_kawasan_admin_can_request_revision_verify_and_publish(): void
    {
        [$adminDesa, $potential] = $this->assignedPotential();
        $proposal = EProposal::factory()->create(['potensi_desa_id' => $potential->id, 'submitted_by' => $adminDesa->id]);
        [$reviewer] = $this->assignedReviewer($potential->village);

        $this->actingAs($reviewer)->post(route('admin.eproposal.request-revision', $proposal), ['catatan' => 'Lengkapi data pendukung.'])->assertRedirect();
        $proposal->refresh();
        $this->assertSame(EProposalStatus::PerluRevisi, $proposal->status);
        $this->assertDatabaseHas('review_eproposal', ['eproposal_id' => $proposal->id, 'status_sesudah' => EProposalStatus::PerluRevisi->value, 'reviewer_id' => $reviewer->id]);

        $this->actingAs($adminDesa)->post(route('admin.eproposal.resubmit', $proposal), ['catatan_pengaju' => 'Data sudah dilengkapi.'])->assertRedirect();
        $this->actingAs($reviewer)->post(route('admin.eproposal.verify', $proposal))->assertRedirect();
        $proposal->refresh();
        $this->assertSame(EProposalStatus::Terverifikasi, $proposal->status);
        $this->assertNotNull($proposal->verified_at);

        $this->actingAs($reviewer)->post(route('admin.eproposal.publish', $proposal))->assertRedirect();
        $proposal->refresh();
        $this->assertSame(EProposalStatus::Published, $proposal->status);
        $this->assertSame(PotensiStatus::Published, $potential->refresh()->status);
        $this->assertNotNull($potential->published_at);
        $this->assertDatabaseHas('review_eproposal', ['eproposal_id' => $proposal->id, 'status_sesudah' => EProposalStatus::Published->value, 'reviewer_id' => $reviewer->id]);
    }

    public function test_revision_note_is_required_and_admin_desa_cannot_review(): void
    {
        [$adminDesa, $potential] = $this->assignedPotential();
        $proposal = EProposal::factory()->create(['potensi_desa_id' => $potential->id, 'submitted_by' => $adminDesa->id]);
        [$reviewer] = $this->assignedReviewer($potential->village);

        $this->actingAs($reviewer)->post(route('admin.eproposal.request-revision', $proposal), ['catatan' => ''])->assertInvalid(['catatan']);
        $this->actingAs($adminDesa)->post(route('admin.eproposal.verify', $proposal))->assertForbidden();
    }

    public function test_kawasan_scope_and_public_workflow_are_protected(): void
    {
        [$adminDesa, $potential] = $this->assignedPotential();
        $proposal = EProposal::factory()->create(['potensi_desa_id' => $potential->id, 'submitted_by' => $adminDesa->id]);
        $otherReviewer = User::factory()->adminKawasan()->create();
        $otherKawasan = KawasanPerdesaan::factory()->create();
        $otherKawasan->adminUsers()->attach($otherReviewer);

        $this->actingAs($otherReviewer)->get(route('admin.eproposal.show', $proposal))->assertNotFound();
        $this->actingAs($otherReviewer)->post(route('admin.eproposal.verify', $proposal))->assertNotFound();
    }

    public function test_invalid_transition_is_rejected_by_service(): void
    {
        $proposal = EProposal::factory()->create();
        $reviewer = User::factory()->adminKawasan()->create();

        $this->expectException(ValidationException::class);
        app(EProposalWorkflowService::class)->publish($proposal, $reviewer);
    }

    public function test_each_transition_notifies_submitter_once(): void
    {
        Notification::fake();
        [$adminDesa, $potential] = $this->assignedPotential();
        $proposal = EProposal::factory()->create(['potensi_desa_id' => $potential->id, 'submitted_by' => $adminDesa->id]);
        [$reviewer] = $this->assignedReviewer($potential->village);
        $service = app(EProposalWorkflowService::class);

        $service->requestRevision($proposal, $reviewer, 'Tambahkan sumber data.');
        Notification::assertSentTo($adminDesa, EProposalStatusChanged::class, fn (EProposalStatusChanged $notification): bool => $notification->status === EProposalStatus::PerluRevisi);
        $service->resubmit($proposal->refresh(), $adminDesa, 'Sudah diperbaiki.');
        $service->verify($proposal->refresh(), $reviewer);
        $service->publish($proposal->refresh(), $reviewer);
        Notification::assertSentToTimes($adminDesa, EProposalStatusChanged::class, 4);
    }

    public function test_publish_rolls_back_every_partial_change_when_a_step_fails(): void
    {
        [$adminDesa, $potential] = $this->assignedPotential();
        $proposal = EProposal::factory()->verified()->create(['potensi_desa_id' => $potential->id, 'submitted_by' => $adminDesa->id]);
        $reviewer = User::factory()->adminKawasan()->create();
        $activity = $this->mock(RecordActivity::class);
        $activity->shouldReceive('execute')->once()->andThrow(new RuntimeException('audit unavailable'));

        try {
            (new EProposalWorkflowService($activity))->publish($proposal, $reviewer);
            $this->fail('Publish seharusnya gagal.');
        } catch (RuntimeException $exception) {
            $this->assertSame('audit unavailable', $exception->getMessage());
        }

        $this->assertSame(EProposalStatus::Terverifikasi, $proposal->refresh()->status);
        $this->assertSame(PotensiStatus::Draft, $potential->refresh()->status);
        $this->assertDatabaseMissing('review_eproposal', ['eproposal_id' => $proposal->id, 'aksi' => 'published']);
    }

    #[TestWith([EProposalStatus::PerluRevisi, 'publish', EProposalStatus::Published])]
    #[TestWith([EProposalStatus::Published, 'verify', EProposalStatus::Terverifikasi])]
    #[TestWith([EProposalStatus::Published, 'requestRevision', EProposalStatus::PerluRevisi])]
    public function test_invalid_terminal_and_revision_transitions_have_no_side_effects(EProposalStatus $from, string $action, EProposalStatus $to): void
    {
        $proposal = EProposal::factory()->create(['status' => $from]);
        $reviewer = User::factory()->superAdmin()->create();
        $service = app(EProposalWorkflowService::class);

        try {
            $action === 'requestRevision'
                ? $service->requestRevision($proposal, $reviewer, 'Catatan tidak boleh mengubah status terminal.')
                : $service->{$action}($proposal, $reviewer);
            $this->fail('Transisi tidak valid seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertSame(['status' => [sprintf('Perubahan status %s ke %s tidak diizinkan.', $from->label(), $to->label())]], $exception->errors());
        }

        $this->assertSame($from, $proposal->refresh()->status);
        $this->assertDatabaseCount('review_eproposal', 0);
        $this->assertDatabaseCount('notifications', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    /** @return array{User, PotensiDesa} */
    private function assignedPotential(): array
    {
        $admin = User::factory()->adminDesa()->create();
        $village = Desa::factory()->create();
        $admin->desas()->attach($village);
        $potential = PotensiDesa::factory()->create(['desa_id' => $village->id, 'kategori_potensi_id' => KategoriPotensi::factory()]);

        return [$admin, $potential];
    }

    /** @return array{User, KawasanPerdesaan} */
    private function assignedReviewer(Desa $village): array
    {
        $reviewer = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->desas()->attach($village);
        $kawasan->adminUsers()->attach($reviewer);

        return [$reviewer, $kawasan];
    }
}
