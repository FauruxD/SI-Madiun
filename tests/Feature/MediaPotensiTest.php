<?php

namespace Tests\Feature;

use App\Enums\TipeMediaPotensi;
use App\Models\Desa;
use App\Models\MediaPotensi;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaPotensiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_assigned_admin_can_upload_private_image_and_activity_is_recorded(): void
    {
        Storage::fake('local');
        [$admin, $potential] = $this->assigned();

        $this->actingAs($admin)->post(route('potensi-desa.media.store', $potential), [
            'tipe_media' => TipeMediaPotensi::Image->value,
            'judul' => 'Panen kopi', 'urutan' => 1, 'file' => UploadedFile::fake()->image('kopi.jpg'),
        ])->assertRedirect();

        $media = MediaPotensi::query()->firstOrFail();
        $this->assertSame(TipeMediaPotensi::Image, $media->tipe_media);
        Storage::disk('local')->assertExists($media->file_path);
        $this->assertDatabaseHas('activity_logs', ['action' => 'potential.media.uploaded', 'subject_id' => $media->id]);
    }

    public function test_multiple_images_keep_explicit_order(): void
    {
        Storage::fake('local');
        [$admin, $potential] = $this->assigned();

        foreach ([3, 1] as $order) {
            $this->actingAs($admin)->post(route('potensi-desa.media.store', $potential), [
                'tipe_media' => 'IMAGE', 'judul' => 'Foto '.$order, 'urutan' => $order, 'file' => UploadedFile::fake()->image('foto'.$order.'.jpg'),
            ])->assertRedirect();
        }

        $this->assertSame([1, 3], MediaPotensi::query()->orderBy('urutan')->pluck('urutan')->all());
    }

    public function test_invalid_image_is_rejected_with_indonesian_message(): void
    {
        Storage::fake('local');
        [$admin, $potential] = $this->assigned();

        $this->actingAs($admin)->post(route('potensi-desa.media.store', $potential), [
            'tipe_media' => 'IMAGE', 'urutan' => 0, 'file' => UploadedFile::fake()->create('script.pdf', 20, 'application/pdf'),
        ])->assertInvalid(['file' => 'Format foto tidak didukung.']);

        $this->assertDatabaseCount('media_potensi', 0);
    }

    public function test_external_video_url_can_be_saved_without_binary_upload(): void
    {
        [$admin, $potential] = $this->assigned();

        $this->actingAs($admin)->post(route('potensi-desa.media.store', $potential), [
            'tipe_media' => 'VIDEO', 'judul' => 'Video profil', 'urutan' => 0, 'media_url' => 'https://www.youtube.com/watch?v=abc123',
        ])->assertRedirect();

        $this->assertDatabaseHas('media_potensi', [
            'potensi_desa_id' => $potential->id,
            'tipe_media' => TipeMediaPotensi::Video->value,
            'media_url' => 'https://www.youtube.com/watch?v=abc123',
        ]);
    }

    public function test_invalid_video_url_is_rejected(): void
    {
        [$admin, $potential] = $this->assigned();

        $this->actingAs($admin)->post(route('potensi-desa.media.store', $potential), [
            'tipe_media' => 'VIDEO', 'urutan' => 0, 'media_url' => 'javascript:alert(1)',
        ])->assertInvalid(['media_url' => 'Video harus berupa URL HTTP atau HTTPS yang valid.']);
    }

    public function test_media_from_another_potential_cannot_be_deleted(): void
    {
        Storage::fake('local');
        [$admin, $potential] = $this->assigned();
        $other = PotensiDesa::factory()->create();
        $media = MediaPotensi::factory()->for($other, 'potential')->create();
        Storage::disk('local')->put($media->file_path, 'image');

        $this->actingAs($admin)->delete(route('potensi-desa.media.destroy', [$potential, $media]))->assertNotFound();
        $this->assertModelExists($media);
    }

    public function test_unauthorized_admin_cannot_modify_media(): void
    {
        Storage::fake('local');
        [, $potential] = $this->assigned();
        $unassigned = User::factory()->adminDesa()->create();

        $this->actingAs($unassigned)->post(route('potensi-desa.media.store', $potential), [
            'tipe_media' => 'VIDEO', 'urutan' => 0, 'media_url' => 'https://example.com/video',
        ])->assertNotFound();
    }

    public function test_delete_media_removes_storage_file_and_creates_activity_log(): void
    {
        Storage::fake('local');
        [$admin, $potential] = $this->assigned();
        $media = MediaPotensi::factory()->for($potential, 'potential')->create();
        Storage::disk('local')->put($media->file_path, 'image');

        $this->actingAs($admin)->delete(route('potensi-desa.media.destroy', [$potential, $media]))->assertRedirect();

        $this->assertDatabaseMissing('media_potensi', ['id' => $media->id]);
        Storage::disk('local')->assertMissing($media->file_path);
        $this->assertDatabaseHas('activity_logs', ['action' => 'potential.media.removed', 'subject_id' => $media->id]);
    }

    public function test_private_image_endpoint_is_scoped_to_parent(): void
    {
        Storage::fake('local');
        [$admin, $potential] = $this->assigned();
        $other = PotensiDesa::factory()->create();
        $media = MediaPotensi::factory()->for($other, 'potential')->create();
        Storage::disk('local')->put($media->file_path, 'image');

        $this->actingAs($admin)->get(route('potensi-desa.media.image', [$potential, $media]))->assertNotFound();
    }

    /** @return array{User, PotensiDesa} */
    private function assigned(): array
    {
        $admin = User::factory()->adminDesa()->create();
        $village = Desa::factory()->create();
        $admin->desas()->attach($village);
        $potential = PotensiDesa::factory()->create(['desa_id' => $village->id]);

        return [$admin, $potential];
    }
}
