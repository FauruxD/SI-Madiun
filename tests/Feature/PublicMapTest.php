<?php

namespace Tests\Feature;

use App\Enums\StatusUnitUsaha;
use App\Models\Bumdes;
use App\Models\KategoriKawasan;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use App\Models\TitikInformasi;
use App\Models\UnitUsaha;
use App\Models\WebsiteBumdes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PublicMapTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const POINT = ['type' => 'Point', 'coordinates' => [111.52, -7.63]];

    private const POLYGON = ['type' => 'Polygon', 'coordinates' => [[[111.5, -7.6], [111.6, -7.6], [111.6, -7.7], [111.5, -7.6]]]];

    public function test_empty_public_map_has_four_layers_and_no_edit_controls(): void
    {
        $this->get(route('peta.index'))->assertOk()->assertSee('Madiun, dalam peta.')->assertSee('Titik informasi')->assertSee('Belum ada lokasi')->assertDontSee('data-map-editor', false)->assertDontSee('Simpan lokasi');
        $this->getJson(route('peta.data'))->assertExactJson(['type' => 'FeatureCollection', 'features' => [], 'meta' => ['counts' => ['kawasan' => 0, 'bumdes' => 0, 'informasi' => 0, 'potensi' => 0], 'truncated' => []]]);
    }

    public function test_only_established_boundaries_active_information_and_published_potentials_are_public(): void
    {
        $established = KawasanPerdesaan::factory()->ditetapkan()->create(['geometri' => self::POLYGON]);
        $draft = KawasanPerdesaan::factory()->create(['geometri' => self::POLYGON]);
        $archived = KawasanPerdesaan::factory()->ditetapkan()->create(['geometri' => self::POLYGON]);
        $archived->delete();
        $visible = TitikInformasi::factory()->create(['kawasan_id' => $established->id]);
        TitikInformasi::factory()->create(['kawasan_id' => $draft->id]);
        TitikInformasi::factory()->create(['kawasan_id' => $archived->id]);
        TitikInformasi::factory()->create(['kawasan_id' => $established->id, 'is_active' => false]);
        $published = PotensiDesa::factory()->published()->create(['geometri' => self::POINT]);
        PotensiDesa::factory()->create(['geometri' => self::POINT]);
        $removed = PotensiDesa::factory()->published()->create(['geometri' => self::POINT]);
        $removed->delete();

        $features = $this->getJson(route('peta.data'))->assertOk()->assertJsonCount(3, 'features')->json('features');
        $this->assertSame(['kawasan-'.$established->id, 'informasi-'.$visible->id, 'potensi-'.$published->id], array_column($features, 'id'));
        $this->assertSame(route('katalog-potensi.show', $published->id, false), $features[2]['properties']['url']);
    }

    public function test_bumdes_popup_is_minimal_and_links_only_active_website_with_no_lazy_loads(): void
    {
        $bumdes = Bumdes::factory()->create(['geometri' => self::POINT, 'email' => 'PRIVATE@example.test', 'telepon' => 'PRIVATE-PHONE', 'deskripsi' => 'PRIVATE-PROFILE']);
        $website = WebsiteBumdes::factory()->create(['bumdes_id' => $bumdes->id]);
        UnitUsaha::factory()->create(['bumdes_id' => $bumdes->id, 'nama' => 'Unit aktif', 'status' => StatusUnitUsaha::Aktif]);
        UnitUsaha::factory()->create(['bumdes_id' => $bumdes->id, 'nama' => 'Unit nonaktif PRIVATE', 'status' => StatusUnitUsaha::Nonaktif]);
        Model::preventLazyLoading();
        try {
            $response = $this->getJson(route('peta.data'))->assertOk()->assertJsonPath('features.0.properties.url', null);
            $response->assertExactJsonStructure(['type', 'features' => ['*' => ['type', 'id', 'geometry' => ['type', 'coordinates'], 'properties' => ['layer', 'name', 'summary', 'url', 'color', 'categories']]], 'meta' => ['counts' => ['kawasan', 'bumdes', 'informasi', 'potensi'], 'truncated']]);
            $this->assertStringContainsString('Unit aktif', $response->json('features.0.properties.summary'));
            $this->assertStringNotContainsString('PRIVATE', $response->getContent());
            $website->update(['is_active' => true]);
            $this->getJson(route('peta.data'))->assertJsonPath('features.0.properties.url', route('public.bumdes.show', $website->subdomain, false));
            $bumdes->delete();
            $this->getJson(route('peta.data'))->assertJsonCount(0, 'features');
        } finally {
            Model::preventLazyLoading(false);
        }
    }

    public function test_multi_category_boundaries_expose_all_categories_with_stable_display_color(): void
    {
        $kawasan = KawasanPerdesaan::factory()->ditetapkan()->create(['geometri' => self::POLYGON]);
        $categories = [KategoriKawasan::factory()->create(['nama' => 'Wisata']), KategoriKawasan::factory()->create(['nama' => 'Agro'])];
        $kawasan->categories()->attach(array_map(fn ($category) => $category->id, $categories));
        $first = $this->getJson(route('peta.data'))->assertOk()->json('features.0.properties');
        $second = $this->getJson(route('peta.data'))->json('features.0.properties');
        $this->assertSame(['Agro', 'Wisata'], array_column($first['categories'], 'name'));
        $this->assertSame($first['color'], $second['color']);
        $this->get(route('peta.index'))->assertSee('Multi-kategori')->assertSee('Agro')->assertSee('Wisata');
    }

    public function test_missing_invalid_geometry_is_omitted_and_untrusted_names_are_escaped(): void
    {
        Bumdes::factory()->create(['geometri' => ['type' => 'Point', 'coordinates' => [999, -7]]]);
        Bumdes::factory()->create();
        $name = '<script>alert("map")</script>';
        Bumdes::factory()->create(['geometri' => self::POINT, 'nama' => $name]);
        $this->getJson(route('peta.data'))->assertJsonCount(1, 'features');
        $this->get(route('peta.index'))->assertSee($name)->assertDontSee($name, false)->assertDontSee('</script><script>', false);
    }

    public function test_feature_limit_is_bounded_and_explicit_not_silent(): void
    {
        config(['map.max_features_per_layer' => 2]);
        Bumdes::factory()->count(3)->create(['geometri' => self::POINT]);
        $this->getJson(route('peta.data'))->assertJsonCount(2, 'features')->assertJsonPath('meta.truncated', ['bumdes']);
        $this->get(route('peta.index'))->assertSee('Peta dibatasi 2 lokasi per layer');
    }
}
