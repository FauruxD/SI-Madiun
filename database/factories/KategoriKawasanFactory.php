<?php

namespace Database\Factories;

use App\Models\KategoriKawasan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<KategoriKawasan> */
class KategoriKawasanFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'nama' => Str::title($name),
            'kode' => Str::of($name)->upper()->replaceMatches('/[^A-Z0-9]+/', '_')->trim('_')->value(),
            'deskripsi' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }
}
