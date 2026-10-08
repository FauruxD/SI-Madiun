<?php

namespace Database\Factories;

use App\Enums\EProposalStatus;
use App\Models\EProposal;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EProposal>
 */
class EProposalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'potensi_desa_id' => PotensiDesa::factory(),
            'submitted_by' => User::factory()->adminDesa(),
            'nomor_proposal' => null,
            'status' => EProposalStatus::Diajukan,
            'catatan_pengaju' => fake()->optional()->sentence(),
            'submitted_at' => now(),
            'verified_at' => null,
            'published_at' => null,
        ];
    }

    public function revisionRequired(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => EProposalStatus::PerluRevisi]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => EProposalStatus::Terverifikasi, 'verified_at' => now()]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => EProposalStatus::Published, 'verified_at' => now(), 'published_at' => now()]);
    }
}
