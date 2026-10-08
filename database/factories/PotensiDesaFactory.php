<?php

namespace Database\Factories;

use App\Enums\PotensiStatus;
use App\Models\Desa;
use App\Models\KategoriPotensi;
use App\Models\PotensiDesa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PotensiDesa>
 */
class PotensiDesaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'desa_id' => Desa::factory(),
            'kategori_potensi_id' => KategoriPotensi::factory(),
            'bumdes_id' => null,
            'judul' => fake()->sentence(5),
            'deskripsi' => fake()->paragraphs(2, true),
            'data_pendukung' => fake()->optional()->paragraph(),
            'kebutuhan_investasi' => fake()->optional()->randomFloat(2, 1000000, 500000000),
            'status' => PotensiStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PotensiStatus::Published,
            'published_at' => now(),
        ]);
    }
}
