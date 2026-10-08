<?php

namespace Database\Factories;

use App\Models\Bumdes;
use App\Models\PengurusBumdes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PengurusBumdes>
 */
class PengurusBumdesFactory extends Factory
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
            'nama' => fake()->name(),
            'jabatan' => fake()->randomElement(['Penasehat', 'Pengawas', 'Direktur']),
            'foto_path' => null,
            'nomor_telepon' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'urutan' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }
}
