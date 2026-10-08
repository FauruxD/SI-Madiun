<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HomeRedirectTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_open_the_public_home_page(): void
    {
        $this->get('/')->assertOk()->assertSee('Desa tumbuh dari data yang terbuka.');
    }

    public function test_authenticated_user_can_open_the_public_home_page_and_return_to_workspace(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')->assertOk()->assertSee('Ruang kerja')->assertSee(route('dashboard'), false);
    }
}
