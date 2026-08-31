<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_update_own_profile_and_change_is_audited(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Nama Diperbarui',
            'username' => 'nama.baru',
            'email' => 'baru@example.test',
            'phone' => '081234567890',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'name' => 'Nama Diperbarui',
            'username' => 'nama.baru',
            'email' => 'baru@example.test',
        ]);
        $this->assertDatabaseHas(ActivityLog::class, ['user_id' => $user->id, 'action' => 'profile.updated']);
    }

    public function test_profile_email_and_username_remain_unique(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'username' => $other->username,
            'email' => $other->email,
        ])->assertInvalid(['username', 'email']);
    }
}
