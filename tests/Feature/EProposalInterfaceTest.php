<?php

namespace Tests\Feature;

use App\Enums\EProposalStatus;
use App\Enums\PotensiStatus;
use App\Models\Desa;
use App\Models\EProposal;
use App\Models\KawasanPerdesaan;
use App\Models\MediaPotensi;
use App\Models\PotensiDesa;
use App\Models\ReviewEProposal;
use App\Models\User;
use App\Services\EProposalWorkflowService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EProposalInterfaceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_preview_contains_real_scoped_data_and_gallery(): void
    {
        [$admin, $potential] = $this->assignedPotential();
        $image = MediaPotensi::factory()->create(['potensi_desa_id' => $potential->id]);
        $other = PotensiDesa::factory()->create(['judul' => 'Potensi di luar wilayah']);

        $this->actingAs($admin)->get(route('admin.eproposal.create', ['potensi_desa_id' => $potential->id]))
            ->assertSee($potential->deskripsi)
            ->assertSee($potential->data_pendukung)
            ->assertSee('Rp 250.000.000')
            ->assertSee(route('potensi-desa.media.image', [$potential, $image]))
            ->assertSee('Kirim E-Proposal')
            ->assertDontSee($other->judul);
    }

    public function test_cross_village_preview_and_proposal_detail_return_404(): void
    {
        [$admin] = $this->assignedPotential();
        $other = EProposal::factory()->create();

        $this->actingAs($admin)->get(route('admin.eproposal.create', ['potensi_desa_id' => $other->potensi_desa_id]))->assertNotFound();
        $this->get(route('admin.eproposal.show', $other))->assertNotFound();
    }

    public function test_proposal_list_renders_categories_without_lazy_loading(): void
    {
        [$admin, $potential] = $this->assignedPotential();
        EProposal::factory()->create(['potensi_desa_id' => $potential->id, 'submitted_by' => $admin->id]);
        EProposal::factory()->create([
            'potensi_desa_id' => PotensiDesa::factory()->create(['desa_id' => $potential->desa_id])->id,
            'submitted_by' => $admin->id,
        ]);
        Model::preventLazyLoading();

        try {
            $this->actingAs($admin)->get(route('admin.eproposal.index'))->assertOk()->assertSee($potential->category->nama);
        } finally {
            Model::preventLazyLoading(false);
        }
    }

    public function test_review_queue_filters_by_village_status_and_title_without_scope_leak(): void
    {
        $reviewer = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->adminUsers()->attach($reviewer);
        $villages = Desa::factory()->count(2)->sequence(
            ['nama' => 'Desa Filter Terpilih'],
            ['nama' => 'Desa Filter Lain'],
        )->create();
        $kawasan->desas()->attach($villages->modelKeys());
        $selected = PotensiDesa::factory()->create(['desa_id' => $villages[0]->id, 'judul' => 'Kopi terpilih']);
        $excluded = PotensiDesa::factory()->create(['desa_id' => $villages[1]->id, 'judul' => 'Kopi tidak terpilih']);
        EProposal::factory()->create(['potensi_desa_id' => $selected->id]);
        EProposal::factory()->create(['potensi_desa_id' => $excluded->id]);
        $outsideVillage = Desa::factory()->create(['nama' => 'Desa Di Luar Assignment']);
        $outside = EProposal::factory()->create([
            'potensi_desa_id' => PotensiDesa::factory()->create(['desa_id' => $outsideVillage->id])->id,
        ]);

        $this->actingAs($reviewer)->get(route('admin.eproposal.review.index', [
            'desa_id' => $villages[0]->id, 'status' => EProposalStatus::Diajukan->value, 'search' => 'Kopi',
        ]))->assertSee('Kopi terpilih')->assertDontSee('Kopi tidak terpilih')->assertDontSee($outside->potential->judul)->assertSee('Diajukan')
            ->assertSee($villages[0]->nama)->assertSee($villages[1]->nama)->assertDontSee($outside->potential->village->nama);

        $this->get(route('admin.eproposal.review.index', ['search' => "' OR 1=1 --"]))
            ->assertSee('Queue review kosong.')->assertDontSee('Kopi terpilih');
        $this->get(route('admin.eproposal.review.index', ['desa_id' => $outside->potential->desa_id]))
            ->assertSee('Queue review kosong.')->assertDontSee($outside->potential->judul);
    }

    public function test_admin_desa_cannot_open_reviewer_queue(): void
    {
        [$admin] = $this->assignedPotential();

        $this->actingAs($admin)->get(route('admin.eproposal.review.index'))->assertForbidden();
    }

    public function test_submitter_can_read_only_their_own_database_notification(): void
    {
        [$admin, $potential] = $this->assignedPotential();
        app(EProposalWorkflowService::class)->submit($potential, $admin);
        $notification = $admin->notifications()->firstOrFail();
        $otherAdmin = User::factory()->adminDesa()->create();

        $this->actingAs($otherAdmin)->patch(route('admin.eproposal.notifications.update', $notification->id))->assertNotFound();
        $this->assertNull($notification->refresh()->read_at);
        $this->actingAs($admin)->get(route('admin.eproposal.index'))->assertSee('Proposal berhasil diajukan.');
        $this->patch(route('admin.eproposal.notifications.update', $notification->id))->assertRedirectToRoute('admin.eproposal.index');
        $this->assertNotNull($notification->refresh()->read_at);
        $this->get(route('admin.eproposal.index'))->assertSee('Tidak ada notifikasi yang belum dibaca.');
    }

    public function test_generic_status_update_cannot_bypass_or_reverse_proposal_workflow(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $proposal = EProposal::factory()->create();
        $potential = $proposal->potential;

        $this->actingAs($superAdmin)->put(route('potensi-desa.update', $potential), [
            ...$this->potentialPayload($potential), 'status' => PotensiStatus::Published->value,
        ])->assertInvalid(['status']);
        $this->assertSame(PotensiStatus::Draft, $potential->refresh()->status);
        $this->assertSame(EProposalStatus::Diajukan, $proposal->refresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);

        $this->put(route('potensi-desa.update', $potential), $this->potentialPayload($potential))->assertRedirect();
        $this->assertSame(PotensiStatus::Draft, $potential->refresh()->status);
        $this->get(route('potensi-desa.edit', $potential))->assertSee('Status dikelola melalui workflow E-Proposal');
    }

    public function test_potential_with_proposal_cannot_be_archived_and_break_history(): void
    {
        [$admin, $potential] = $this->assignedPotential();
        EProposal::factory()->create(['potensi_desa_id' => $potential->id, 'submitted_by' => $admin->id]);

        $this->actingAs($admin)->delete(route('potensi-desa.destroy', $potential))->assertForbidden();
        $this->assertDatabaseHas('potensi_desa', ['id' => $potential->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'potential.deleted']);
    }

    public function test_legacy_published_potential_is_not_silently_retracted_by_submission(): void
    {
        [$admin, $potential] = $this->assignedPotential();
        $potential->update(['status' => PotensiStatus::Published, 'published_at' => now()]);

        $this->actingAs($admin)->post(route('admin.eproposal.store'), ['potensi_desa_id' => $potential->id])
            ->assertInvalid(['potensi_desa_id' => 'Potensi yang sudah Published tidak dapat diajukan. Aturan proposal untuk publikasi lama belum ditetapkan.']);
        $this->assertSame(PotensiStatus::Published, $potential->refresh()->status);
        $this->assertDatabaseCount('eproposal', 0);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_proposal_preview_and_history_escape_user_content(): void
    {
        [$admin, $potential] = $this->assignedPotential();
        $dangerous = '<script>alert("xss")</script>';
        $potential->update(['judul' => $dangerous, 'deskripsi' => $dangerous, 'data_pendukung' => $dangerous]);
        $proposal = EProposal::factory()->create(['potensi_desa_id' => $potential->id, 'submitted_by' => $admin->id, 'catatan_pengaju' => $dangerous]);
        ReviewEProposal::factory()->create(['eproposal_id' => $proposal->id, 'catatan' => $dangerous]);

        $this->actingAs($admin)->get(route('admin.eproposal.show', $proposal))->assertSee($dangerous)->assertDontSee($dangerous, false);
    }

    public function test_public_workflow_and_download_endpoints_require_login(): void
    {
        $proposal = EProposal::factory()->create();

        $this->post(route('admin.eproposal.verify', $proposal))->assertRedirectToRoute('login');
        $this->post(route('admin.eproposal.publish', $proposal))->assertRedirectToRoute('login');
        $this->get(route('admin.eproposal.download', $proposal))->assertRedirectToRoute('login');
        $this->assertSame(EProposalStatus::Diajukan, $proposal->refresh()->status);
        $this->assertDatabaseCount('review_eproposal', 0);
    }

    /** @return array{User, PotensiDesa} */
    private function assignedPotential(): array
    {
        $admin = User::factory()->adminDesa()->create();
        $village = Desa::factory()->create();
        $admin->desas()->attach($village);
        $potential = PotensiDesa::factory()->create([
            'desa_id' => $village->id, 'kebutuhan_investasi' => '250000000.00',
            'data_pendukung' => 'Kapasitas produksi 12 ton per tahun.',
        ]);

        return [$admin, $potential];
    }

    /** @return array<string, mixed> */
    private function potentialPayload(PotensiDesa $potential): array
    {
        return $potential->only(['desa_id', 'kategori_potensi_id', 'bumdes_id', 'judul', 'deskripsi', 'data_pendukung', 'kebutuhan_investasi']);
    }
}
