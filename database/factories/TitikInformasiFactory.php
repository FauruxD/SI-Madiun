<?php

namespace Database\Factories;

use App\Models\KawasanPerdesaan;
use App\Models\TitikInformasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TitikInformasi> */
class TitikInformasiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kawasan_id' => KawasanPerdesaan::factory(),
            'nama' => fake()->unique()->words(3, true),
            'jenis' => 'pertemuan',
            'deskripsi' => fake()->sentence(),
            'geometri' => ['type' => 'Point', 'coordinates' => [111.52, -7.63]],
            'is_active' => true,
        ];
    }
}
