<?php

namespace Tests\Feature;

use App\Enums\JenisDokumenBumdes;
use App\Models\ActivityLog;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\DokumenBumdes;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DokumenBumdesTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_authorized_admin_can_upload_ad_with_structured_summary_and_metadata(): void
    {
        $this->assertDocumentCanBeUploaded(JenisDokumenBumdes::Ad);
    }

    public function test_authorized_admin_can_upload_art(): void
    {
        $this->assertDocumentCanBeUploaded(JenisDokumenBumdes::Art);
    }

    public function test_invalid_document_mime_is_rejected_without_storing_file(): void
    {
        [$admin, $bumdes] = $this->assignedAdminAndBumdes();

        $this->actingAs($admin)->post(route('bumdes.dokumen.store', $bumdes), [
            'jenis_dokumen' => JenisDokumenBumdes::Ad->value,
            'file' => UploadedFile::fake()->create('malware.exe', 20, 'application/x-msdownload'),
        ])->assertInvalid(['file']);

        $this->assertDatabaseCount(DokumenBumdes::class, 0);
        Storage::disk('local')->assertDirectoryEmpty('documents/bumdes');
    }

    public function test_unauthorized_admin_cannot_download_internal_document(): void
    {
        $unassignedAdmin = User::factory()->adminDesa()->create();
        $bumdes = Bumdes::factory()->create();
        $document = DokumenBumdes::factory()->for($bumdes, 'bumdes')->create();
        Storage::disk('local')->put($document->file_path, 'private');

        $this->actingAs($unassignedAdmin)
            ->get(route('bumdes.dokumen.download', [$bumdes, $document]))
            ->assertNotFound();
    }

    public function test_document_id_from_another_bumdes_cannot_be_downloaded_or_deleted(): void
    {
        [$admin, $bumdes] = $this->assignedAdminAndBumdes();
        $otherDocument = DokumenBumdes::factory()->create();

        $this->actingAs($admin)->get(route('bumdes.dokumen.download', [$bumdes, $otherDocument]))->assertNotFound();
        $this->actingAs($admin)->delete(route('bumdes.dokumen.destroy', [$bumdes, $otherDocument]))->assertNotFound();
    }

    public function test_removing_document_deletes_metadata_file_and_records_activity(): void
    {
        [$admin, $bumdes] = $this->assignedAdminAndBumdes();
        $document = DokumenBumdes::factory()->for($bumdes, 'bumdes')->create();
        Storage::disk('local')->put($document->file_path, 'private');

        $this->actingAs($admin)->delete(route('bumdes.dokumen.destroy', [$bumdes, $document]))->assertRedirect();

        $this->assertDatabaseMissing(DokumenBumdes::class, ['id' => $document->id]);
        Storage::disk('local')->assertMissing($document->file_path);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.document.removed', 'subject_id' => $document->id]);
    }

    private function assertDocumentCanBeUploaded(JenisDokumenBumdes $type): void
    {
        [$admin, $bumdes] = $this->assignedAdminAndBumdes();

        $this->actingAs($admin)->post(route('bumdes.dokumen.store', $bumdes), [
            'jenis_dokumen' => $type->value,
            'nomor_dokumen' => '01/'.$type->value.'/2026',
            'tanggal_dokumen' => '2026-08-17',
            'ringkasan_maksud_tujuan' => 'Mengembangkan ekonomi desa.',
            'ringkasan_modal_awal' => '150000000.25',
            'ringkasan_pembagian_shu' => '40% untuk desa dan 60% pengembangan usaha.',
            'file' => UploadedFile::fake()->create('legalitas.pdf', 128, 'application/pdf'),
        ])->assertRedirect();

        $document = DokumenBumdes::query()->firstOrFail();
        $this->assertSame($type, $document->jenis_dokumen);
        $this->assertSame('legalitas.pdf', $document->nama_file);
        $this->assertSame('application/pdf', $document->mime_type);
        $this->assertSame('150000000.25', $document->ringkasan_modal_awal);
        $this->assertSame($admin->id, $document->uploaded_by);
        Storage::disk('local')->assertExists($document->file_path);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.document.uploaded', 'subject_id' => $document->id]);
    }

    /** @return array{User, Bumdes} */
    private function assignedAdminAndBumdes(): array
    {
        $admin = User::factory()->adminDesa()->create();
        $desa = Desa::factory()->create();
        $admin->desas()->attach($desa);
        $bumdes = Bumdes::factory()->create();
        $bumdes->desas()->attach($desa, ['is_desa_utama' => true]);

        return [$admin, $bumdes];
    }
}
