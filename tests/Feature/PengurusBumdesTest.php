<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\PengurusBumdes;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengurusBumdesTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_authorized_admin_can_create_manager_with_order_photo_and_activity(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)->post(route('bumdes.pengurus.store', $bumdes), [
            ...$this->payload(),
            'urutan' => 3,
            'foto' => UploadedFile::fake()->image('direktur.jpg'),
        ])->assertRedirect();

        $manager = PengurusBumdes::query()->firstOrFail();
        $this->assertSame(3, $manager->urutan);
        Storage::disk('local')->assertExists($manager->foto_path);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.manager.created', 'subject_id' => $manager->id]);
    }

    public function test_authorized_admin_can_update_manager(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $manager = PengurusBumdes::factory()->for($bumdes, 'bumdes')->create();

        $this->actingAs($admin)->put(route('bumdes.pengurus.update', [$bumdes, $manager]), [
            ...$this->payload(),
            'nama' => 'Direktur Baru',
            'is_active' => false,
        ])->assertRedirect();

        $this->assertSame('Direktur Baru', $manager->fresh()->nama);
        $this->assertFalse($manager->fresh()->is_active);
        $this->assertDatabaseHas(ActivityLog::class, ['action' => 'bumdes.manager.updated', 'subject_id' => $manager->id]);
    }

    public function test_manager_from_another_bumdes_cannot_be_modified_by_url_manipulation(): void
    {
        [$admin, $bumdes] = $this->assigned();
        $other = PengurusBumdes::factory()->create();

        $this->actingAs($admin)->put(route('bumdes.pengurus.update', [$bumdes, $other]), $this->payload())->assertNotFound();
    }

    public function test_invalid_manager_photo_is_rejected(): void
    {
        [$admin, $bumdes] = $this->assigned();

        $this->actingAs($admin)->post(route('bumdes.pengurus.store', $bumdes), [
            ...$this->payload(),
            'foto' => UploadedFile::fake()->create('foto.svg', 10, 'image/svg+xml'),
        ])->assertInvalid(['foto']);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return ['nama' => 'Siti Aminah', 'jabatan' => 'Direktur', 'nomor_telepon' => '08123456789', 'email' => 'siti@example.test', 'urutan' => 1, 'is_active' => true];
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
