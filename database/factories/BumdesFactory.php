<?php

namespace Database\Factories;

use App\Enums\JenisBumdes;
use App\Models\Bumdes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bumdes>
 */
class BumdesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'BUMDes '.fake()->unique()->company(),
            'jenis' => JenisBumdes::Bumdes,
            'nomor_registrasi' => fake()->boolean() ? fake()->unique()->numerify('REG-########') : null,
            'tahun_berdiri' => fake()->numberBetween(2015, (int) date('Y')),
            'status_hukum' => fake()->randomElement(['Perdes pendirian', 'Perkades pendirian']),
            'alamat' => fake()->address(),
            'telepon' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->companyEmail(),
            'deskripsi' => fake()->optional()->paragraph(),
        ];
    }

    public function bumdesma(): static
    {
        return $this->state(fn (array $attributes): array => ['jenis' => JenisBumdes::Bumdesma]);
    }
}
