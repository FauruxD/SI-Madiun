<?php

namespace Database\Factories;

use App\Enums\StatusUnitUsaha;
use App\Models\Bumdes;
use App\Models\UnitUsaha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitUsaha>
 */
class UnitUsahaFactory extends Factory
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
            'nama' => 'Unit '.fake()->unique()->company(),
            'deskripsi' => fake()->optional()->sentence(),
            'status' => StatusUnitUsaha::Aktif,
        ];
    }
}
