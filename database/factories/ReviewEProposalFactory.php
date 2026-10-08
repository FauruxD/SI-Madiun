<?php

namespace Database\Factories;

use App\Enums\EProposalStatus;
use App\Models\EProposal;
use App\Models\ReviewEProposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewEProposal>
 */
class ReviewEProposalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eproposal_id' => EProposal::factory(),
            'reviewer_id' => User::factory()->adminKawasan(),
            'status_sebelum' => EProposalStatus::Diajukan,
            'status_sesudah' => EProposalStatus::Terverifikasi,
            'aksi' => 'verified',
            'catatan' => null,
            'reviewed_at' => now(),
        ];
    }
}
