<?php

namespace Database\Factories;

use App\Models\ProdukLayanan;
use App\Models\UnitUsaha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProdukLayanan>
 */
class ProdukLayananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_usaha_id' => UnitUsaha::factory(),
            'nama' => fake()->unique()->words(3, true),
            'kategori' => fake()->optional()->word(),
            'deskripsi' => fake()->sentence(),
            'harga' => fake()->optional()->randomFloat(2, 1000, 10000000),
            'satuan' => fake()->optional()->randomElement(['unit', 'kg', 'paket']),
            'foto_path' => null,
            'is_active' => true,
        ];
    }
}
