<?php

namespace Tests\Feature;

use App\Models\AsetBumdes;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\GaleriBumdes;
use App\Models\KawasanPerdesaan;
use App\Models\KeuanganBumdes;
use App\Models\PengurusBumdes;
use App\Models\ProdukLayanan;
use App\Models\UnitUsaha;
use App\Models\User;
use App\Models\WebsiteBumdes;
use App\Services\CompanyProfilePdfService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CompanyProfilePdfTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_desa_downloads_owned_company_profile_with_required_content_and_audit(): void
    {
        [$profile, $owner] = $this->ownedProfile();
        PengurusBumdes::factory()->create(['bumdes_id' => $profile->id, 'nama' => 'Direktur contoh', 'is_active' => true]);
        $unit = UnitUsaha::factory()->create(['bumdes_id' => $profile->id]);
        ProdukLayanan::factory()->create(['unit_usaha_id' => $unit->id, 'nama' => 'Beras unggulan']);
        AsetBumdes::factory()->create(['bumdes_id' => $profile->id, 'nama' => 'Gudang usaha']);
        KeuanganBumdes::factory()->create(['bumdes_id' => $profile->id, 'tahun' => 2025, 'omzet' => '120000000.00']);

        $response = $this->actingAs($owner)->get(route('bumdes.company-profile.download', $profile));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('Content-Disposition')->assertHeader('Cache-Control', 'no-store, private');
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
        $this->assertDatabaseHas('activity_logs', ['action' => 'bumdes.company-profile.downloaded', 'subject_id' => $profile->id, 'user_id' => $owner->id]);
        $html = app(CompanyProfilePdfService::class)->render($profile)->getDomPDF()->getOptions();
        $this->assertFalse($html->getIsRemoteEnabled());
    }

    public function test_super_admin_can_download_company_profile_outside_any_assignment(): void
    {
        $profile = Bumdes::factory()->create();

        $this->actingAs(User::factory()->superAdmin()->create())->get(route('bumdes.company-profile.download', $profile))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_desa_cannot_download_other_village_company_profile(): void
    {
        [$profile] = $this->ownedProfile();
        $other = User::factory()->adminDesa()->create();
        $other->desas()->attach(Desa::factory()->create());

        $this->actingAs($other)->get(route('bumdes.company-profile.download', $profile))->assertNotFound();

        $this->assertDatabaseMissing('activity_logs', ['action' => 'bumdes.company-profile.downloaded']);
    }

    public function test_admin_kawasan_cannot_download_company_profile_in_assigned_territory(): void
    {
        [$profile, , $desa] = $this->ownedProfile();
        $reviewer = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->desas()->attach($desa);
        $kawasan->adminUsers()->attach($reviewer);

        $this->actingAs($reviewer)->get(route('bumdes.company-profile.download', $profile))->assertForbidden();
    }

    public function test_unauthenticated_admin_download_redirects_to_login(): void
    {
        $profile = Bumdes::factory()->create();

        $this->get(route('bumdes.company-profile.download', $profile))->assertRedirect(route('login'));

        $this->assertDatabaseMissing('activity_logs', ['action' => 'bumdes.company-profile.downloaded']);
    }

    public function test_active_website_does_not_implicitly_publish_company_profile(): void
    {
        $website = WebsiteBumdes::factory()->active()->create();

        $this->get(route('public.bumdes.company-profile', $website->subdomain))->assertNotFound();
        $this->get(route('public.bumdes.show', $website->subdomain))->assertDontSee('Unduh Company Profile');

        $this->assertDatabaseMissing('activity_logs', ['action' => 'bumdes.company-profile.downloaded']);
    }

    public function test_pdf_template_contains_required_sections_and_escapes_all_model_text(): void
    {
        $profile = Bumdes::factory()->create(['nama' => '<script>private()</script>']);
        $profile->load(['desas', 'managers', 'businessUnits.products', 'assets', 'financialSummaries']);

        $html = view('pdf.company-profile', ['bumdes' => $profile, 'gallery' => collect()])->render();

        foreach (['Company Profile', 'Struktur organisasi', 'katalog produk', 'Data aset', 'Ringkasan kinerja keuangan', 'Galeri'] as $section) {
            $this->assertStringContainsString($section, $html);
        }
        $this->assertStringContainsString(e($profile->nama), $html);
        $this->assertStringNotContainsString($profile->nama, $html);
        $this->assertStringContainsString('Dokumentasi foto belum tersedia.', $html);
    }

    public function test_valid_gallery_photo_is_embedded_and_renderer_disables_active_content(): void
    {
        Storage::fake(config('bumdes.images.disk'));
        $profile = Bumdes::factory()->create();
        $path = UploadedFile::fake()->image('gallery.png', 800, 600)->store('images/bumdes/'.$profile->id.'/galeri', config('bumdes.images.disk'));
        GaleriBumdes::factory()->create(['bumdes_id' => $profile->id, 'file_path' => $path]);

        $pdf = app(CompanyProfilePdfService::class)->render($profile);
        $output = $pdf->output();

        $this->assertStringStartsWith('%PDF', $output);
        $this->assertStringContainsString('/Subtype /Image', $output);
        $options = $pdf->getDomPDF()->getOptions();
        $this->assertFalse($options->getIsRemoteEnabled());
        $this->assertFalse($options->getIsPhpEnabled());
        $this->assertFalse($options->getIsJavascriptEnabled());
    }

    #[TestWith(['../secret.png'])]
    #[TestWith(['https://external.test/photo.png'])]
    #[TestWith(['images/bumdes/999/galeri/other.png'])]
    #[TestWith(['images/bumdes/1/galeri/missing.png'])]
    public function test_unsafe_or_missing_gallery_photo_does_not_break_pdf(string $path): void
    {
        Storage::fake(config('bumdes.images.disk'));
        $profile = Bumdes::factory()->create();
        GaleriBumdes::factory()->create(['bumdes_id' => $profile->id, 'file_path' => $path]);

        $output = app(CompanyProfilePdfService::class)->render($profile)->output();

        $this->assertStringStartsWith('%PDF', $output);
        $this->assertStringNotContainsString('/Subtype /Image', $output);
    }

    /** @return array{Bumdes, User, Desa} */
    private function ownedProfile(): array
    {
        $desa = Desa::factory()->create();
        $owner = User::factory()->adminDesa()->create();
        $owner->desas()->attach($desa);
        $profile = Bumdes::factory()->create();
        $profile->desas()->attach($desa, ['is_desa_utama' => true]);

        return [$profile, $owner, $desa];
    }
}
