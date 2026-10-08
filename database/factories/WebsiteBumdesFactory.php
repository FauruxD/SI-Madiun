<?php

namespace Database\Factories;

use App\Models\Bumdes;
use App\Models\WebsiteBumdes;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WebsiteBumdes> */
class WebsiteBumdesFactory extends Factory
{
    protected $model = WebsiteBumdes::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['bumdes_id' => Bumdes::factory(), 'subdomain' => fake()->unique()->slug(2), 'is_active' => false];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true, 'activated_at' => now()]);
    }
}
