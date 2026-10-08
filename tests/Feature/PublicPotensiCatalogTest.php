<?php

namespace Tests\Feature;

use App\Models\KategoriPotensi;
use App\Models\MediaPotensi;
use App\Models\PotensiDesa;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicPotensiCatalogTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_published_potential_is_visible_in_public_catalog(): void
    {
        $potential = PotensiDesa::factory()->published()->create(['judul' => 'Agrowisata Sawah Madiun']);

        $this->get(route('katalog-potensi.index'))->assertOk()->assertSee($potential->judul);
    }

    public function test_draft_potential_is_not_visible_in_public_catalog_or_detail(): void
    {
        $potential = PotensiDesa::factory()->create(['judul' => 'Draf Rahasia']);

        $this->get(route('katalog-potensi.index'))->assertOk()->assertDontSee($potential->judul);
        $this->get(route('katalog-potensi.show', $potential))->assertNotFound();
    }

    public function test_public_search_matches_title_and_village(): void
    {
        $potential = PotensiDesa::factory()->published()->create(['judul' => 'Kopi Lereng Wilis']);
        $other = PotensiDesa::factory()->published()->create(['judul' => 'Kerajinan Bambu']);

        $this->get(route('katalog-potensi.index', ['search' => 'Kopi Lereng']))->assertSee($potential->judul)->assertDontSee($other->judul);
        $this->get(route('katalog-potensi.index', ['search' => $potential->village->nama]))->assertSee($potential->judul);
    }

    public function test_public_category_filter_is_database_side_and_excludes_other_category(): void
    {
        $agro = KategoriPotensi::factory()->create(['nama' => 'Pertanian']);
        $wisata = KategoriPotensi::factory()->create(['nama' => 'Wisata']);
        $included = PotensiDesa::factory()->published()->create(['kategori_potensi_id' => $agro->id, 'judul' => 'Kebun Organik']);
        $excluded = PotensiDesa::factory()->published()->create(['kategori_potensi_id' => $wisata->id, 'judul' => 'Air Terjun']);

        $this->get(route('katalog-potensi.index', ['kategori' => $agro->id]))->assertSee($included->judul)->assertDontSee($excluded->judul);
    }

    public function test_public_catalog_paginates_and_keeps_query_parameters(): void
    {
        $category = KategoriPotensi::factory()->create();

        foreach (range(1, 13) as $number) {
            PotensiDesa::factory()->published()->create([
                'kategori_potensi_id' => $category->id,
                'judul' => 'Potensi Terbit '.$number,
            ]);
        }

        $response = $this->get(route('katalog-potensi.index', ['search' => 'Potensi', 'kategori' => $category->id]));

        $response->assertOk()->assertSee('page=2')->assertSee('search=Potensi', false)->assertSee('kategori='.$category->id, false);
    }

    public function test_published_detail_contains_gallery_investment_and_related_bumdes_text(): void
    {
        $potential = PotensiDesa::factory()->published()->create([
            'judul' => 'Sentra Olahan Pangan', 'data_pendukung' => 'Kapasitas 2 ton per bulan', 'kebutuhan_investasi' => '150000000.00',
        ]);
        $video = MediaPotensi::factory()->video()->for($potential, 'potential')->create(['judul' => 'Video lapangan']);

        $this->get(route('katalog-potensi.show', $potential))->assertOk()
            ->assertSee($potential->judul)
            ->assertSee('Kapasitas 2 ton per bulan')
            ->assertSee('Rp 150.000.000')
            ->assertSee($video->judul)
            ->assertSee('Buka video');
    }

    public function test_public_image_endpoint_only_serves_published_parent(): void
    {
        Storage::fake('local');
        $published = PotensiDesa::factory()->published()->create();
        $media = MediaPotensi::factory()->for($published, 'potential')->create();
        Storage::disk('local')->put($media->file_path, 'image');
        $draft = PotensiDesa::factory()->create();
        $draftMedia = MediaPotensi::factory()->for($draft, 'potential')->create();

        $this->get(route('katalog-potensi.media', [$published, $media]))->assertOk();
        $this->get(route('katalog-potensi.media', [$draft, $draftMedia]))->assertNotFound();
    }

    public function test_public_catalog_escapes_user_content(): void
    {
        $potential = PotensiDesa::factory()->published()->create(['judul' => '<script>alert(1)</script>']);

        $this->get(route('katalog-potensi.index'))->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_guest_cannot_access_admin_potential_endpoint(): void
    {
        $this->get(route('potensi-desa.index'))->assertRedirectToRoute('login');
    }
}
