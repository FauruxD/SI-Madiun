<?php

namespace Database\Factories;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kecamatan>
 */
class KecamatanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kabupaten_id' => Kabupaten::factory(),
            'kode' => fake()->unique()->numerify('##.##'),
            'nama' => 'Kecamatan '.fake()->citySuffix(),
        ];
    }
}
