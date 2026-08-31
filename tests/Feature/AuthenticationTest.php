<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_login_with_email_and_login_is_audited(): void
    {
        $user = User::factory()->adminDesa()->create(['password' => 'Password123']);

        $response = $this->post('/login', [
            'identifier' => strtoupper($user->email),
            'password' => 'Password123',
        ]);

        $response->assertRedirectToRoute('dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas(ActivityLog::class, ['user_id' => $user->id, 'action' => 'auth.login']);
    }

    public function test_user_can_login_with_username(): void
    {
        $user = User::factory()->adminKawasan()->create(['username' => 'kawasan.demo', 'password' => 'Password123']);

        $this->post('/login', ['identifier' => 'kawasan.demo', 'password' => 'Password123'])
            ->assertRedirectToRoute('dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create(['password' => 'Password123']);

        $this->post('/login', ['identifier' => $user->email, 'password' => 'Password123'])
            ->assertInvalid(['identifier']);

        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', ['identifier' => 'unknown@example.test', 'password' => 'wrong'])
                ->assertInvalid(['identifier']);
        }

        $this->post('/login', ['identifier' => 'unknown@example.test', 'password' => 'wrong'])
            ->assertTooManyRequests();
    }

    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirectToRoute('login');

        $this->assertGuest();
        $this->assertDatabaseHas(ActivityLog::class, ['user_id' => $user->id, 'action' => 'auth.logout']);
    }

    public function test_password_reset_link_can_be_requested_and_used(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ])->assertRedirectToRoute('login');

            return true;
        });

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
    }
}
