<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_html_response_has_baseline_security_headers(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
    }

    public function test_json_response_has_baseline_security_headers(): void
    {
        $this->getJson(route('peta.data'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_login_form_keeps_csrf_protection_and_security_headers(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_public_pdf_download_is_rate_limited(): void
    {
        $route = collect(app('router')->getRoutes()->getRoutes())
            ->first(fn ($route) => $route->getName() === 'public.proposal.download');

        $this->assertNotNull($route);
        $this->assertContains('throttle:20,1', $route->gatherMiddleware());
    }
}
