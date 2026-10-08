<?php

namespace Tests\Feature;

use App\Enums\JenisDokumenKawasan;
use App\Models\DokumenKawasan;
use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DokumenKawasanTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_super_admin_can_upload_sk_bupati_with_metadata(): void
    {
        $this->assertDocumentCanBeUploaded(JenisDokumenKawasan::SkBupati);
    }

    public function test_super_admin_can_upload_tkpkp_document(): void
    {
        $this->assertDocumentCanBeUploaded(JenisDokumenKawasan::DokumenTkpkp);
    }

    public function test_invalid_document_file_is_rejected(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kawasan = KawasanPerdesaan::factory()->create();

        $this->actingAs($superAdmin)->post(route('admin.kawasan.dokumen.store', $kawasan), [
            'jenis_dokumen' => JenisDokumenKawasan::SkBupati->value,
            'file' => UploadedFile::fake()->create('program.exe', 20, 'application/x-msdownload'),
        ])->assertInvalid(['file']);

        config()->set('kawasan.documents.max_size_kb', 1);
        $this->actingAs($superAdmin)->post(route('admin.kawasan.dokumen.store', $kawasan), [
            'jenis_dokumen' => JenisDokumenKawasan::SkBupati->value,
            'file' => UploadedFile::fake()->create('terlalu-besar.pdf', 2, 'application/pdf'),
        ])->assertInvalid(['file']);

        $this->assertDatabaseCount(DokumenKawasan::class, 0);
        Storage::disk('local')->assertDirectoryEmpty('kawasan-documents');
    }

    public function test_unauthorized_user_cannot_download_internal_document(): void
    {
        $adminDesa = User::factory()->adminDesa()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $document = DokumenKawasan::factory()->for($kawasan, 'kawasan')->create();
        Storage::disk('local')->put($document->file_path, 'private');

        $this->actingAs($adminDesa)
            ->get(route('kawasan.dokumen.download', [$kawasan, $document]))
            ->assertNotFound();
    }

    public function test_assigned_admin_can_download_internal_document_but_cannot_delete_it(): void
    {
        $adminKawasan = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->adminUsers()->attach($adminKawasan);
        $document = DokumenKawasan::factory()->for($kawasan, 'kawasan')->create();
        Storage::disk('local')->put($document->file_path, 'private');

        $this->actingAs($adminKawasan)
            ->get(route('kawasan.dokumen.download', [$kawasan, $document]))
            ->assertDownload($document->nama_file);

        $this->actingAs($adminKawasan)
            ->delete(route('admin.kawasan.dokumen.destroy', [$kawasan, $document]))
            ->assertForbidden();
    }

    public function test_deleting_document_removes_metadata_and_file(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $document = DokumenKawasan::factory()->for($kawasan, 'kawasan')->create();
        Storage::disk('local')->put($document->file_path, 'private');

        $this->actingAs($superAdmin)
            ->delete(route('admin.kawasan.dokumen.destroy', [$kawasan, $document]))
            ->assertRedirectToRoute('admin.kawasan.show', $kawasan);

        $this->assertDatabaseMissing(DokumenKawasan::class, ['id' => $document->id]);
        Storage::disk('local')->assertMissing($document->file_path);
    }

    private function assertDocumentCanBeUploaded(JenisDokumenKawasan $jenis): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $file = UploadedFile::fake()->create('dasar-hukum.pdf', 128, 'application/pdf');

        $this->actingAs($superAdmin)->post(route('admin.kawasan.dokumen.store', $kawasan), [
            'jenis_dokumen' => $jenis->value,
            'nomor_dokumen' => '188.45/100/2026',
            'tanggal_dokumen' => '2026-08-17',
            'file' => $file,
        ])->assertRedirectToRoute('admin.kawasan.show', $kawasan);

        $document = DokumenKawasan::query()->firstOrFail();
        $this->assertSame($jenis, $document->jenis_dokumen);
        $this->assertSame('dasar-hukum.pdf', $document->nama_file);
        $this->assertSame('application/pdf', $document->mime_type);
        $this->assertSame($superAdmin->id, $document->uploaded_by);
        $this->assertGreaterThan(0, $document->file_size);
        $this->assertStringNotContainsString('dasar-hukum', $document->file_path);
        Storage::disk('local')->assertExists($document->file_path);
        $this->assertDatabaseHas('activity_logs', ['action' => 'kawasan.document.uploaded', 'subject_id' => $document->id]);
    }
}
