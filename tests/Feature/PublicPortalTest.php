<?php

namespace Tests\Feature;

use App\Enums\StatusKemitraan;
use App\Models\Desa;
use App\Models\EProposal;
use App\Models\KawasanPerdesaan;
use App\Models\KemitraanBumdes;
use App\Models\PotensiDesa;
use App\Models\WebsiteBumdes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicPortalTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_home_uses_the_provided_madiun_image_as_hero_background(): void
    {
        $response = $this->get(route('public.home'));

        $response
            ->assertSee('src="'.asset('images/madiun2.jpg').'"', false)
            ->assertSee('fetchpriority="high"', false);

        $this->assertFileExists(public_path('images/madiun2.jpg'));
    }

    public function test_home_only_exposes_records_that_pass_their_publication_gates(): void
    {
        $established = KawasanPerdesaan::factory()->ditetapkan()->create(['nama' => 'Kawasan Publik Lereng Wilis']);
        KawasanPerdesaan::factory()->create(['nama' => 'Kawasan Draf Rahasia']);
        $published = PotensiDesa::factory()->published()->create(['judul' => 'Kopi Kare Terbit']);
        PotensiDesa::factory()->create(['judul' => 'Potensi Draf Rahasia']);
        $activeWebsite = WebsiteBumdes::factory()->active()->create();
        $activeWebsite->bumdes->update(['nama' => 'BUMDes Publik Sejahtera']);
        $inactiveWebsite = WebsiteBumdes::factory()->create();
        $inactiveWebsite->bumdes->update(['nama' => 'BUMDes Nonaktif Rahasia']);
        $opportunity = KemitraanBumdes::factory()->dibutuhkan()->create([
            'bumdes_id' => $activeWebsite->bumdes_id,
            'jenis_kemitraan' => 'Distribusi regional',
        ]);
        KemitraanBumdes::factory()->dibutuhkan()->create([
            'bumdes_id' => $inactiveWebsite->bumdes_id,
            'jenis_kemitraan' => 'Kemitraan Rahasia',
        ]);
        $proposal = EProposal::factory()->published()->for($published, 'potential')->create(['nomor_proposal' => 'PROP/PUBLIK/001']);
        $draftPotential = PotensiDesa::factory()->published()->create(['judul' => 'Proposal Belum Terbit']);
        EProposal::factory()->for($draftPotential, 'potential')->create(['nomor_proposal' => 'PROP/DRAF/002']);

        $response = $this->get(route('public.home'));

        $response->assertOk()
            ->assertSee($established->nama)
            ->assertSee($published->judul)
            ->assertSee($activeWebsite->bumdes->nama)
            ->assertSee($opportunity->jenis_kemitraan)
            ->assertSee($proposal->nomor_proposal)
            ->assertDontSee('Kawasan Draf Rahasia')
            ->assertDontSee('Potensi Draf Rahasia')
            ->assertDontSee('BUMDes Nonaktif Rahasia')
            ->assertDontSee('Kemitraan Rahasia')
            ->assertDontSee('PROP/DRAF/002');
    }

    public function test_public_kawasan_directory_hides_drafts_and_escapes_content(): void
    {
        $visible = KawasanPerdesaan::factory()->ditetapkan()->create([
            'nama' => 'Kawasan <script>alert("xss")</script>',
            'deskripsi' => 'Kolaborasi desa yang telah ditetapkan.',
        ]);
        $visible->desas()->attach(Desa::factory()->create());
        $draft = KawasanPerdesaan::factory()->create(['nama' => 'Kawasan Draf Internal']);

        $this->get(route('public.kawasan.index'))
            ->assertSee($visible->nama)
            ->assertDontSee($visible->nama, false)
            ->assertDontSee($draft->nama);
        $this->get(route('public.kawasan.show', $visible))->assertOk()->assertSee($visible->deskripsi);
        $this->get(route('public.kawasan.show', $draft))->assertNotFound();
    }

    public function test_public_bumdes_directory_only_lists_active_websites_and_supports_search(): void
    {
        $desa = Desa::factory()->create(['nama' => 'Desa Sumber Makmur']);
        $visible = WebsiteBumdes::factory()->active()->create();
        $visible->bumdes->update(['nama' => 'BUMDes <script>Terbuka</script>']);
        $visible->bumdes->desas()->attach($desa, ['is_desa_utama' => true]);
        $excluded = WebsiteBumdes::factory()->create();
        $excluded->bumdes->update(['nama' => 'BUMDes Belum Aktif']);

        $this->get(route('public.bumdes.index', ['search' => 'Sumber Makmur']))
            ->assertSee($visible->bumdes->nama)
            ->assertDontSee($visible->bumdes->nama, false)
            ->assertDontSee($excluded->bumdes->nama)
            ->assertSee(route('public.bumdes.show', $visible->subdomain), false);
        $this->get(route('public.bumdes.index', ['search' => 'Tidak Ada']))
            ->assertSee('Belum ada BUMDes yang cocok.');
    }

    public function test_home_has_bounded_queries_and_does_not_lazy_load_relationships(): void
    {
        $website = WebsiteBumdes::factory()->active()->create();
        KemitraanBumdes::factory()->count(2)->create([
            'bumdes_id' => $website->bumdes_id,
            'status' => StatusKemitraan::Dibutuhkan,
        ]);
        PotensiDesa::factory()->published()->count(4)->create();
        KawasanPerdesaan::factory()->ditetapkan()->count(4)->create();
        DB::flushQueryLog();
        DB::enableQueryLog();
        Model::preventLazyLoading();

        try {
            $this->get(route('public.home'))->assertOk();
            $queryCount = count(DB::getQueryLog());
        } finally {
            Model::preventLazyLoading(false);
            DB::disableQueryLog();
        }

        $this->assertLessThanOrEqual(30, $queryCount, 'Beranda publik melebihi batas 30 query.');
    }

    public function test_public_navigation_and_about_page_are_available_without_authentication(): void
    {
        $response = $this->get(route('public.about'));

        $response->assertOk()
            ->assertSee('Tentang sistem')
            ->assertSee(route('public.home'), false)
            ->assertSee(route('public.kawasan.index'), false)
            ->assertSee(route('katalog-potensi.index'), false)
            ->assertSee(route('public.bumdes.index'), false)
            ->assertSee(route('peta.index'), false)
            ->assertSee('aria-label="Navigasi publik"', false);
    }
}
