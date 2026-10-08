<?php

namespace Tests\Feature;

use App\Models\Bumdes;
use App\Models\EProposal;
use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_demo_domain_seed_is_idempotent_and_populates_the_public_portal(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(1, KawasanPerdesaan::query()->where('nama', 'DEMO - Kawasan Lereng Wilis')->count());
        $this->assertSame(1, Bumdes::query()->where('nama', 'DEMO - BUMDes Margo Makmur')->count());
        $this->assertSame(1, PotensiDesa::query()->where('judul', 'DEMO - Kopi Lereng Wilis')->count());
        $this->assertSame(1, EProposal::query()->where('nomor_proposal', 'DEMO/EP/001/2026')->count());
        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('DEMO - Kawasan Lereng Wilis')
            ->assertSee('DEMO - BUMDes Margo Makmur')
            ->assertSee('DEMO - Kopi Lereng Wilis')
            ->assertSee('DEMO/EP/001/2026');
    }
}
