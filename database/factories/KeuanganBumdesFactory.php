<?php

namespace Database\Factories;

use App\Models\Bumdes;
use App\Models\KeuanganBumdes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KeuanganBumdes>
 */
class KeuanganBumdesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bumdes_id' => Bumdes::factory(),
            'tahun' => fake()->numberBetween(2020, (int) date('Y')),
            'modal_awal' => fake()->optional()->randomFloat(2, 1000000, 500000000),
            'omzet' => fake()->optional()->randomFloat(2, 1000000, 1000000000),
            'shu' => fake()->optional()->randomFloat(2, 1000000, 250000000),
            'kontribusi_pades' => fake()->optional()->randomFloat(2, 1000000, 100000000),
        ];
    }
}
