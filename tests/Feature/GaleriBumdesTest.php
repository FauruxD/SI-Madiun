<?php

namespace Tests\Feature;

use App\Enums\TipeMediaBumdes;
use App\Models\ActivityLog;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\GaleriBumdes;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GaleriBumdesTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_authorized_admin_can_upload_private_gallery_image(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)->post(route('bumdes.galeri.store', $bumdes), [
            'judul' => 'Kegiatan produksi', 'urutan' => 2, 'file' => UploadedFile::fake()->image('produksi.jpg'),
        ])->assertRedirect();

        $image = GaleriBumdes::query()->firstOrFail();
        $this->assertSame(TipeMediaBumdes::Image, $image->tipe_media);
        $this->assertSame(2, $image->urutan);
        Storage::disk('local')->assertExists($image->file_path);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.gallery.created', 'subject_id' => $image->id]);
    }

    public function test_unauthorized_admin_cannot_view_private_gallery_image(): void
    {
        $unassigned = User::factory()->adminDesa()->create();
        $image = GaleriBumdes::factory()->create();
        Storage::disk('local')->put($image->file_path, 'image');

        $this->actingAs($unassigned)->get(route('bumdes.media.galeri', [$image->bumdes_id, $image]))->assertNotFound();
    }

    public function test_removing_gallery_image_deletes_file(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $image = GaleriBumdes::factory()->for($bumdes, 'bumdes')->create();
        Storage::disk('local')->put($image->file_path, 'image');

        $this->actingAs($admin)->delete(route('bumdes.galeri.destroy', [$bumdes, $image]))->assertRedirect();

        $this->assertDatabaseMissing(GaleriBumdes::class, ['id' => $image->id]);
        Storage::disk('local')->assertMissing($image->file_path);
    }

    /** @return array{User, Bumdes} */
    private function assigned(): array
    {
        $admin = User::factory()->adminDesa()->create();
        $desa = Desa::factory()->create();
        $admin->desas()->attach($desa);
        $bumdes = Bumdes::factory()->create();
        $bumdes->desas()->attach($desa, ['is_desa_utama' => true]);

        return [$admin, $bumdes];
    }
}
