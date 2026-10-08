<?php

namespace Tests\Feature;

use App\Enums\KawasanStatus;
use App\Models\Desa;
use App\Models\KategoriKawasan;
use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class KawasanAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_non_super_admin_cannot_create_kawasan(): void
    {
        $adminKawasan = User::factory()->adminKawasan()->create();
        $adminDesa = User::factory()->adminDesa()->create();
        $payload = $this->validPayload();

        $this->actingAs($adminKawasan)->post(route('admin.kawasan.store'), $payload)->assertForbidden();
        $this->actingAs($adminDesa)->post(route('admin.kawasan.store'), $payload)->assertForbidden();
    }

    public function test_admin_desa_cannot_access_kawasan_management(): void
    {
        $adminDesa = User::factory()->adminDesa()->create();
        $kawasan = KawasanPerdesaan::factory()->create();

        $this->actingAs($adminDesa)->get(route('admin.kawasan.index'))->assertForbidden();
        $this->actingAs($adminDesa)->get(route('admin.kawasan.edit', $kawasan))->assertForbidden();
        $this->actingAs($adminDesa)->getJson(route('admin.kawasan.options.desa'))->assertForbidden();
    }

    public function test_guest_cannot_access_admin_kawasan_routes(): void
    {
        $this->get(route('admin.kawasan.index'))->assertRedirectToRoute('login');
        $this->post(route('admin.kawasan.store'), [])->assertRedirectToRoute('login');
    }

    public function test_only_admin_kawasan_role_can_be_assigned(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $adminDesa = User::factory()->adminDesa()->create();
        $payload = $this->validPayload();
        $payload['admin_user_ids'] = [$adminDesa->id];

        $this->actingAs($superAdmin)
            ->post(route('admin.kawasan.store'), $payload)
            ->assertInvalid(['admin_user_ids.0']);
    }

    public function test_admin_kawasan_can_only_view_assigned_kawasan(): void
    {
        $adminKawasan = User::factory()->adminKawasan()->create();
        $assigned = KawasanPerdesaan::factory()->create();
        $other = KawasanPerdesaan::factory()->create();
        $assigned->adminUsers()->attach($adminKawasan);

        $this->actingAs($adminKawasan)
            ->get(route('kawasan.show', $assigned))
            ->assertOk()
            ->assertSee($assigned->nama);

        $this->actingAs($adminKawasan)
            ->get(route('kawasan.show', $other))
            ->assertNotFound();
    }

    public function test_changing_url_ids_cannot_bypass_document_or_kawasan_scope(): void
    {
        $firstAdmin = User::factory()->adminKawasan()->create();
        $secondAdmin = User::factory()->adminKawasan()->create();
        $firstKawasan = KawasanPerdesaan::factory()->create();
        $secondKawasan = KawasanPerdesaan::factory()->create();
        $firstKawasan->adminUsers()->attach($firstAdmin);
        $secondKawasan->adminUsers()->attach($secondAdmin);
        $document = $secondKawasan->documents()->create([
            'jenis_dokumen' => 'SK_BUPATI',
            'nama_file' => 'sk.pdf',
            'file_path' => 'kawasan-documents/secret.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 10,
            'uploaded_by' => $secondAdmin->id,
        ]);

        $this->actingAs($firstAdmin)->get(route('kawasan.show', $secondKawasan))->assertNotFound();
        $this->actingAs($firstAdmin)
            ->get(route('kawasan.dokumen.download', [$secondKawasan, $document]))
            ->assertNotFound();
    }

    public function test_changing_user_role_detaches_stale_kawasan_assignments(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $managedUser = User::factory()->adminKawasan()->create();
        $kawasan = KawasanPerdesaan::factory()->create();
        $managedUser->kawasans()->attach($kawasan);

        $this->actingAs($superAdmin)->put(route('admin.users.update', $managedUser), [
            'name' => $managedUser->name,
            'username' => $managedUser->username,
            'email' => $managedUser->email,
            'role' => 'ADMIN_DESA',
            'is_active' => true,
            'desa_ids' => [Desa::factory()->create()->id],
        ])->assertRedirectToRoute('admin.users.index');

        $this->assertCount(0, $managedUser->fresh()->kawasans);
    }

    /** @return array<string, mixed> */
    private function validPayload(): array
    {
        return [
            'nama' => 'Kawasan Uji',
            'status' => KawasanStatus::Draft->value,
            'desa_ids' => [Desa::factory()->create()->id],
            'category_ids' => [KategoriKawasan::factory()->create()->id],
        ];
    }
}
