<?php

namespace Database\Factories;

use App\Models\KategoriPotensi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KategoriPotensi>
 */
class KategoriPotensiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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
