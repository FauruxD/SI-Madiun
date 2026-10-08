<?php

namespace Database\Factories;

use App\Enums\KawasanStatus;
use App\Models\KawasanPerdesaan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<KawasanPerdesaan> */
class KawasanPerdesaanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => 'Kawasan '.fake()->unique()->city(),
            'deskripsi' => fake()->optional()->sentence(),
            'tanggal_penetapan' => null,
            'status' => KawasanStatus::Draft,
        ];
    }

    public function ditetapkan(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => KawasanStatus::Ditetapkan,
            'tanggal_penetapan' => fake()->date(),
        ]);
    }
}
