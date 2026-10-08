<?php

namespace Tests\Feature;

use App\Actions\CreateBumdesAction;
use App\Actions\RecordActivity;
use App\Enums\StatusUnitUsaha;
use App\Models\Desa;
use App\Models\GaleriBumdes;
use App\Models\KawasanPerdesaan;
use App\Models\PengurusBumdes;
use App\Models\PotensiDesa;
use App\Models\ProdukLayanan;
use App\Models\UnitUsaha;
use App\Models\User;
use App\Models\WebsiteBumdes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class BumdesWebsiteTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_new_profile_gets_unique_inactive_default_website_inside_creation_transaction(): void
    {
        $actor = User::factory()->superAdmin()->create();
        $desa = Desa::factory()->create();
        $data = ['nama' => str_repeat('Usaha Desa ', 20), 'jenis' => 'BUMDES', 'desa_ids' => [$desa->id], 'desa_utama_id' => $desa->id];

        $first = app(CreateBumdesAction::class)->execute($data, $actor);
        $second = app(CreateBumdesAction::class)->execute($data, $actor);

        $this->assertFalse($first->website->is_active);
        $this->assertNotSame($first->website->subdomain, $second->website->subdomain);
        $this->assertLessThanOrEqual(63, strlen($first->website->subdomain));
        $this->assertDatabaseCount('website_bumdes', 2);
    }

    public function test_admin_desa_can_activate_update_and_deactivate_owned_website_with_audit(): void
    {
        [$website, $owner] = $this->ownedWebsite();
        $this->actingAs($owner)->get(route('bumdes.website.edit', $website->bumdes))->assertSee('Website nonaktif');

        $this->actingAs($owner)->put(route('bumdes.website.update', $website->bumdes), ['subdomain' => '  Usaha-Brumbun  ', 'custom_domain' => '  USAHABRUMBUN.ID  ', 'is_active' => '1'])->assertRedirect();

        $this->assertDatabaseHas('website_bumdes', ['id' => $website->id, 'subdomain' => 'usaha-brumbun', 'custom_domain' => 'usahabrumbun.id', 'is_active' => true]);
        $this->assertNotNull($website->fresh()->activated_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'bumdes.website.updated', 'subject_id' => $website->bumdes_id]);
        $this->get(route('public.bumdes.show', 'usaha-brumbun'))->assertSee($website->bumdes->nama);

        $this->put(route('bumdes.website.update', $website->bumdes), ['subdomain' => 'usaha-brumbun', 'custom_domain' => null, 'is_active' => 0])->assertRedirect();
        $this->get(route('public.bumdes.show', 'usaha-brumbun'))->assertNotFound();
        $this->assertNull($website->fresh()->activated_at);
    }

    public function test_cross_desa_settings_and_updates_return_404_without_changes(): void
    {
        [$website] = $this->ownedWebsite();
        $other = User::factory()->adminDesa()->create();
        $other->desas()->attach(Desa::factory()->create());

        $this->actingAs($other)->get(route('bumdes.website.edit', $website->bumdes))->assertNotFound();
        $this->put(route('bumdes.website.update', $website->bumdes), $this->payload($website))->assertNotFound();

        $this->assertFalse($website->fresh()->is_active);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'bumdes.website.updated']);
    }

    public function test_admin_kawasan_cannot_manage_website_even_inside_assigned_territory(): void
    {
        [$website, , $desa] = $this->ownedWebsite();
        $reviewer = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $kawasan->desas()->attach($desa);
        $kawasan->adminUsers()->attach($reviewer);

        $this->actingAs($reviewer)->get(route('bumdes.website.edit', $website->bumdes))->assertForbidden();
        $this->put(route('bumdes.website.update', $website->bumdes), $this->payload($website))->assertForbidden();
        $this->assertFalse($website->fresh()->is_active);
    }

    public function test_missing_authentication_redirects_settings_and_updates_to_login(): void
    {
        [$website] = $this->ownedWebsite();

        $this->get(route('bumdes.website.edit', $website->bumdes))->assertRedirect(route('login'));
        $this->put(route('bumdes.website.update', $website->bumdes), $this->payload($website))->assertRedirect(route('login'));

        $this->assertFalse($website->fresh()->is_active);
    }

    #[TestWith(['subdomain', 'bad.name'])]
    #[TestWith(['subdomain', '-invalid'])]
    #[TestWith(['subdomain', 'admin'])]
    #[TestWith(['subdomain', ''])]
    #[TestWith(['subdomain', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'])]
    #[TestWith(['custom_domain', 'https://usaha.id/path'])]
    #[TestWith(['custom_domain', 'usaha.id:443'])]
    #[TestWith(['custom_domain', '127.0.0.1'])]
    #[TestWith(['custom_domain', 'localhost'])]
    #[TestWith(['custom_domain', ['invalid']])]
    #[TestWith(['custom_domain', 'other.bumdes.localhost'])]
    #[TestWith(['is_active', 'published'])]
    public function test_invalid_setting_is_rejected_without_mutation(string $field, mixed $value): void
    {
        [$website, $owner] = $this->ownedWebsite();

        $this->actingAs($owner)->put(route('bumdes.website.update', $website->bumdes), [...$this->payload($website), $field => $value])->assertSessionHasErrors($field);

        $this->assertFalse($website->fresh()->is_active);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'bumdes.website.updated']);
    }

    public function test_subdomain_and_custom_domain_cannot_be_reused_by_another_profile(): void
    {
        [$website, $owner] = $this->ownedWebsite();
        WebsiteBumdes::factory()->create(['subdomain' => 'occupied', 'custom_domain' => 'occupied.id']);

        $this->actingAs($owner)->put(route('bumdes.website.update', $website->bumdes), ['subdomain' => 'occupied', 'custom_domain' => 'occupied.id', 'is_active' => 1])->assertSessionHasErrors(['subdomain', 'custom_domain']);

        $this->assertFalse($website->fresh()->is_active);
    }

    public function test_user_cannot_supply_domain_verification_or_pdf_publication_flags(): void
    {
        [$website, $owner] = $this->ownedWebsite();

        $this->actingAs($owner)->put(route('bumdes.website.update', $website->bumdes), [...$this->payload($website), 'verified_custom_domains' => ['fake.id'], 'company_profile_published' => 1])->assertSessionHasErrors(['verified_custom_domains', 'company_profile_published']);

        $this->assertFalse($website->fresh()->is_active);
    }

    public function test_public_projection_excludes_private_and_inactive_data_and_eager_loads_relationships(): void
    {
        $website = WebsiteBumdes::factory()->active()->create();
        $unit = UnitUsaha::factory()->create(['bumdes_id' => $website->bumdes_id, 'status' => StatusUnitUsaha::Aktif]);
        ProdukLayanan::factory()->create(['unit_usaha_id' => $unit->id, 'nama' => 'Produk publik', 'is_active' => true]);
        ProdukLayanan::factory()->create(['unit_usaha_id' => $unit->id, 'nama' => 'Produk rahasia', 'is_active' => false]);
        $inactive = UnitUsaha::factory()->create(['bumdes_id' => $website->bumdes_id, 'status' => StatusUnitUsaha::Nonaktif]);
        ProdukLayanan::factory()->create(['unit_usaha_id' => $inactive->id, 'nama' => 'Unit nonaktif', 'is_active' => true]);
        PengurusBumdes::factory()->create(['bumdes_id' => $website->bumdes_id, 'nama' => 'Pengurus publik', 'nomor_telepon' => 'PRIVATE-TELEPHONE', 'email' => 'private@example.test', 'is_active' => true]);
        PengurusBumdes::factory()->create(['bumdes_id' => $website->bumdes_id, 'nama' => 'Pengurus nonaktif', 'is_active' => false]);
        Model::preventLazyLoading();

        try {
            $this->get(route('public.bumdes.show', $website->subdomain))->assertSee('Produk publik')->assertSee('Pengurus publik')->assertDontSee('Produk rahasia')->assertDontSee('Unit nonaktif')->assertDontSee('Pengurus nonaktif')->assertDontSee('PRIVATE-TELEPHONE')->assertDontSee('private@example.test')->assertDontSee('Unduh Company Profile');
        } finally {
            Model::preventLazyLoading(false);
        }
    }

    public function test_public_content_is_escaped_and_product_list_is_paginated(): void
    {
        $website = WebsiteBumdes::factory()->active()->create();
        $website->bumdes->update(['nama' => '<script>alert("name")</script>', 'deskripsi' => '<script>alert("description")</script>']);
        $unit = UnitUsaha::factory()->create(['bumdes_id' => $website->bumdes_id, 'status' => StatusUnitUsaha::Aktif]);
        ProdukLayanan::factory()->count(13)->create(['unit_usaha_id' => $unit->id, 'is_active' => true]);

        $response = $this->get(route('public.bumdes.show', $website->subdomain));

        $response->assertSee($website->bumdes->nama)->assertDontSee($website->bumdes->nama, false)->assertSee('products_page=2', false);
        $this->assertCount(12, $response->viewData('products'));
    }

    public function test_private_manager_photos_and_inactive_product_images_are_not_publicly_accessible(): void
    {
        $website = WebsiteBumdes::factory()->active()->create();
        $manager = PengurusBumdes::factory()->create(['bumdes_id' => $website->bumdes_id]);
        $unit = UnitUsaha::factory()->create(['bumdes_id' => $website->bumdes_id]);
        $product = ProdukLayanan::factory()->create(['unit_usaha_id' => $unit->id, 'is_active' => false]);

        $this->get('/website-bumdes/'.$website->subdomain.'/media/manager/'.$manager->id)->assertNotFound();
        $this->get(route('public.bumdes.media', [$website->subdomain, 'product', $product->id]))->assertNotFound();
    }

    public function test_missing_corrupt_and_cross_folder_photos_are_omitted_without_breaking_public_page(): void
    {
        Storage::fake(config('bumdes.images.disk'));
        $website = WebsiteBumdes::factory()->active()->create();
        $path = 'images/bumdes/'.$website->bumdes_id.'/galeri/corrupt.png';
        Storage::disk(config('bumdes.images.disk'))->put($path, '<script>not an image</script>');
        $photo = GaleriBumdes::factory()->create(['bumdes_id' => $website->bumdes_id, 'file_path' => $path]);
        GaleriBumdes::factory()->create(['bumdes_id' => $website->bumdes_id, 'file_path' => '../private.png']);

        $this->get(route('public.bumdes.media', [$website->subdomain, 'gallery', $photo->id]))->assertNotFound();
        $this->get(route('public.bumdes.show', $website->subdomain))->assertSee('Dokumentasi foto belum tersedia.')->assertDontSee('/media/gallery/', false);
    }

    public function test_subdomain_and_verified_custom_domain_resolve_only_the_associated_active_profile(): void
    {
        $website = WebsiteBumdes::factory()->active()->create(['subdomain' => 'tenant', 'custom_domain' => 'tenant.id']);
        config(['bumdes.website.verified_custom_domains' => ['tenant.id']]);

        $this->get('http://tenant.bumdes.localhost/')->assertSee($website->bumdes->nama);
        $this->get('http://tenant.id/')->assertSee($website->bumdes->nama);
        $this->get('http://unknown.bumdes.localhost/')->assertNotFound();
        $this->get('http://spoofed.id/login')->assertNotFound();

        $website->update(['is_active' => false]);
        $this->get('http://tenant.id/')->assertNotFound();
    }

    public function test_unverified_custom_domain_and_admin_routes_on_tenant_host_are_denied(): void
    {
        WebsiteBumdes::factory()->active()->create(['subdomain' => 'tenant', 'custom_domain' => 'unverified.id']);

        $this->get('http://unverified.id/')->assertNotFound();
        $this->get('http://tenant.bumdes.localhost/login')->assertNotFound();
        $this->post('http://tenant.bumdes.localhost/logout')->assertNotFound();
        $this->get('http://tenant.bumdes.localhost/admin/users')->assertNotFound();
    }

    public function test_parent_scoped_images_reject_cross_bumdes_missing_files_and_deactivated_website(): void
    {
        Storage::fake(config('bumdes.images.disk'));
        $website = WebsiteBumdes::factory()->active()->create();
        $path = UploadedFile::fake()->image('photo.png')->store('images/bumdes/'.$website->bumdes_id.'/galeri', config('bumdes.images.disk'));
        $photo = GaleriBumdes::factory()->create(['bumdes_id' => $website->bumdes_id, 'file_path' => $path]);
        $other = WebsiteBumdes::factory()->active()->create();

        $this->get(route('public.bumdes.media', [$website->subdomain, 'gallery', $photo->id]))->assertOk()->assertHeader('Content-Type', 'image/png')->assertHeader('Cache-Control', 'no-store, private');
        $this->get(route('public.bumdes.media', [$other->subdomain, 'gallery', $photo->id]))->assertNotFound();
        $website->update(['is_active' => false]);
        $this->get(route('public.bumdes.media', [$website->subdomain, 'gallery', $photo->id]))->assertNotFound();
    }

    public function test_archived_profile_has_no_public_page_even_if_website_flag_is_active(): void
    {
        $website = WebsiteBumdes::factory()->active()->create();
        $website->bumdes->delete();

        $this->get(route('public.bumdes.show', $website->subdomain))->assertNotFound();
    }

    public function test_public_potential_links_only_an_active_bumdes_website(): void
    {
        $website = WebsiteBumdes::factory()->active()->create();
        $potential = PotensiDesa::factory()->published()->create(['bumdes_id' => $website->bumdes_id]);

        $this->get(route('katalog-potensi.show', $potential))->assertSee(route('public.bumdes.show', $website->subdomain), false);
        $website->update(['is_active' => false]);
        $this->get(route('katalog-potensi.show', $potential))->assertDontSee(route('public.bumdes.show', $website->subdomain), false);
    }

    public function test_activity_failure_rolls_back_website_changes(): void
    {
        [$website, $owner] = $this->ownedWebsite();
        $this->mock(RecordActivity::class)->shouldReceive('execute')->once()->andThrow(new RuntimeException('Audit unavailable'));

        $this->actingAs($owner)->put(route('bumdes.website.update', $website->bumdes), $this->payload($website))->assertServerError();

        $this->assertFalse($website->fresh()->is_active);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'bumdes.website.updated']);
    }

    public function test_policy_matrix_matches_owner_super_admin_and_kawasan_permissions(): void
    {
        [$website, $owner] = $this->ownedWebsite();
        $super = User::factory()->superAdmin()->create();
        $kawasan = User::factory()->adminKawasan()->create();
        $other = User::factory()->adminDesa()->create();

        foreach (['manageWebsite', 'downloadCompanyProfile'] as $ability) {
            $this->assertTrue(Gate::forUser($owner)->allows($ability, $website->bumdes));
            $this->assertTrue(Gate::forUser($super)->allows($ability, $website->bumdes));
            $this->assertFalse(Gate::forUser($kawasan)->allows($ability, $website->bumdes));
            $this->assertFalse(Gate::forUser($other)->allows($ability, $website->bumdes));
        }
    }

    /** @return array{WebsiteBumdes, User, Desa} */
    private function ownedWebsite(): array
    {
        $desa = Desa::factory()->create();
        $owner = User::factory()->adminDesa()->create();
        $owner->desas()->attach($desa);
        $website = WebsiteBumdes::factory()->create();
        $website->bumdes->desas()->attach($desa, ['is_desa_utama' => true]);

        return [$website, $owner, $desa];
    }

    /** @return array{subdomain: string, custom_domain: null, is_active: int} */
    private function payload(WebsiteBumdes $website): array
    {
        return ['subdomain' => $website->subdomain, 'custom_domain' => null, 'is_active' => 1];
    }
}
