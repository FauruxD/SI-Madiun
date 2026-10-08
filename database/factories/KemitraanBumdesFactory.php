<?php

namespace Database\Factories;

use App\Enums\StatusKemitraan;
use App\Models\Bumdes;
use App\Models\KemitraanBumdes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KemitraanBumdes>
 */
class KemitraanBumdesFactory extends Factory
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
            'nama_mitra' => fake()->company(),
            'jenis_kemitraan' => fake()->randomElement(['Pemasaran', 'Distribusi', 'Permodalan']),
            'deskripsi' => fake()->optional()->sentence(),
            'status' => StatusKemitraan::Berjalan,
            'kebutuhan_investasi' => null,
        ];
    }

    public function dibutuhkan(): static
    {
        return $this->state(fn (array $attributes): array => [
            'nama_mitra' => null,
            'status' => StatusKemitraan::Dibutuhkan,
            'kebutuhan_investasi' => fake()->randomFloat(2, 1000000, 500000000),
        ]);
    }
}
