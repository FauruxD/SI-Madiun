<?php

namespace Database\Factories;

use App\Models\AsetBumdes;
use App\Models\Bumdes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AsetBumdes>
 */
class AsetBumdesFactory extends Factory
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
            'nama' => fake()->words(3, true),
            'jenis' => fake()->randomElement(['Tanah', 'Bangunan', 'Alat produksi']),
            'deskripsi' => fake()->optional()->sentence(),
            'nilai_perkiraan' => fake()->optional()->randomFloat(2, 1000000, 1000000000),
            'status_kepemilikan' => fake()->randomElement(['Milik sendiri', 'Sewa']),
            'kondisi' => fake()->optional()->randomElement(['Baik', 'Perlu perbaikan']),
        ];
    }
}
