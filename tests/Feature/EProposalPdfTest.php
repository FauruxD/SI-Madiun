<?php

namespace Tests\Feature;

use App\Enums\EProposalStatus;
use App\Enums\PotensiStatus;
use App\Models\Desa;
use App\Models\EProposal;
use App\Models\KategoriPotensi;
use App\Models\KawasanPerdesaan;
use App\Models\MediaPotensi;
use App\Models\PotensiDesa;
use App\Models\User;
use App\Services\ProposalPdfService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EProposalPdfTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_kawasan_can_download_verified_proposal_pdf(): void
    {
        [$proposal, $reviewer] = $this->proposalForReviewer(EProposalStatus::Terverifikasi);

        $response = $this->actingAs($reviewer)->get(route('admin.eproposal.download', $proposal));

        $response->assertOk()->assertHeader('content-type', 'application/pdf')->assertHeader('content-disposition');
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_submitted_and_revision_proposals_are_not_downloadable(): void
    {
        [$proposal, $reviewer] = $this->proposalForReviewer(EProposalStatus::Diajukan);
        $this->actingAs($reviewer)->get(route('admin.eproposal.download', $proposal))->assertForbidden();

        $proposal->update(['status' => EProposalStatus::PerluRevisi]);
        $this->actingAs($reviewer)->get(route('admin.eproposal.download', $proposal))->assertForbidden();
    }

    public function test_public_can_download_only_published_proposal_and_catalog_links_it(): void
    {
        [$proposal] = $this->proposalForReviewer(EProposalStatus::Published);
        $proposal->potential->update(['status' => PotensiStatus::Published, 'published_at' => now()]);

        $this->get(route('public.proposal.download', $proposal))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('katalog-potensi.show', $proposal->potential))->assertOk()->assertSee('Unduh Proposal');

        $proposal->update(['status' => EProposalStatus::Terverifikasi]);
        $this->get(route('public.proposal.download', $proposal))->assertNotFound();
    }

    public function test_admin_desa_cannot_download_proposal_pdf(): void
    {
        [$proposal] = $this->proposalForReviewer(EProposalStatus::Terverifikasi);
        $admin = $proposal->submitter;

        $this->actingAs($admin)->get(route('admin.eproposal.download', $proposal))->assertForbidden();
    }

    public function test_super_admin_can_download_verified_proposal_from_any_area(): void
    {
        [$proposal] = $this->proposalForReviewer(EProposalStatus::Terverifikasi);
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get(route('admin.eproposal.download', $proposal))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertDatabaseHas('activity_logs', ['action' => 'eproposal.pdf.downloaded', 'subject_id' => $proposal->id]);
    }

    public function test_other_kawasan_cannot_download_verified_pdf(): void
    {
        [$proposal] = $this->proposalForReviewer(EProposalStatus::Terverifikasi);
        $reviewer = User::factory()->adminKawasan()->create();
        KawasanPerdesaan::factory()->create()->adminUsers()->attach($reviewer);

        $this->actingAs($reviewer)->get(route('admin.eproposal.download', $proposal))->assertNotFound();
        $this->assertDatabaseMissing('activity_logs', ['action' => 'eproposal.pdf.downloaded']);
    }

    public function test_public_pdf_requires_published_potential_as_well_as_proposal(): void
    {
        [$proposal] = $this->proposalForReviewer(EProposalStatus::Published);

        $this->get(route('public.proposal.download', $proposal))->assertNotFound();
        $this->get(route('katalog-potensi.show', $proposal->potential))->assertNotFound();
    }

    public function test_pdf_embeds_valid_local_photo_and_disables_active_and_remote_content(): void
    {
        Storage::fake(config('potensi.images.disk'));
        [$proposal] = $this->proposalForReviewer(EProposalStatus::Terverifikasi);
        $proposal->potential->village->kecamatan->kabupaten->update(['nama' => 'Kabupaten Madiun']);
        $path = UploadedFile::fake()->image('gallery.png', 800, 600)->store('media/potensi/'.$proposal->potensi_desa_id.'/images', config('potensi.images.disk'));
        MediaPotensi::factory()->create(['potensi_desa_id' => $proposal->potensi_desa_id, 'file_path' => $path, 'judul' => 'Galeri terverifikasi']);

        $pdf = app(ProposalPdfService::class)->render($proposal);
        $html = $pdf->getDomPDF()->getDom()->saveHTML();

        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('Galeri terverifikasi', $html);
        $this->assertStringNotContainsString('Kabupaten Kabupaten', $html);
        $this->assertStringStartsWith('%PDF', $pdf->output());
        $options = $pdf->getDomPDF()->getOptions();
        $this->assertFalse($options->getIsRemoteEnabled());
        $this->assertFalse($options->getIsPhpEnabled());
        $this->assertFalse($options->getIsJavascriptEnabled());
    }

    public function test_pdf_skips_missing_corrupt_and_arbitrary_path_images_and_escapes_text(): void
    {
        Storage::fake(config('potensi.images.disk'));
        [$proposal] = $this->proposalForReviewer(EProposalStatus::Terverifikasi);
        $proposal->potential->update(['deskripsi' => '<script>alert("pdf")</script>']);
        $disk = Storage::disk(config('potensi.images.disk'));
        $disk->put('secret.png', UploadedFile::fake()->image('secret.png')->getContent());
        $corrupt = 'media/potensi/'.$proposal->potensi_desa_id.'/images/corrupt.png';
        $disk->put($corrupt, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        foreach (['secret.png', 'media/potensi/'.$proposal->potensi_desa_id.'/images/../../secret.png', $corrupt, 'media/potensi/'.$proposal->potensi_desa_id.'/images/missing.png'] as $path) {
            MediaPotensi::factory()->create(['potensi_desa_id' => $proposal->potensi_desa_id, 'file_path' => $path]);
        }

        $pdf = app(ProposalPdfService::class)->render($proposal);
        $html = $pdf->getDomPDF()->getDom()->saveHTML();

        $this->assertStringNotContainsString('data:image/', $html);
        $this->assertStringNotContainsString('<script>alert("pdf")</script>', $html);
        $this->assertStringContainsString('Foto pendukung belum tersedia atau tidak dapat dibaca.', $html);
        $this->assertStringStartsWith('%PDF', $pdf->output());
    }

    /** @return array{EProposal, User} */
    private function proposalForReviewer(EProposalStatus $status): array
    {
        $admin = User::factory()->adminDesa()->create();
        $village = Desa::factory()->create();
        $admin->desas()->attach($village);
        $potential = PotensiDesa::factory()->create(['desa_id' => $village->id, 'kategori_potensi_id' => KategoriPotensi::factory()]);
        $proposal = EProposal::factory()->create([
            'potensi_desa_id' => $potential->id,
            'submitted_by' => $admin->id,
            'status' => $status,
            'verified_at' => $status->isDownloadable() ? now() : null,
            'published_at' => $status === EProposalStatus::Published ? now() : null,
        ]);
        $reviewer = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->desas()->attach($village);
        $kawasan->adminUsers()->attach($reviewer);

        return [$proposal, $reviewer];
    }
}
