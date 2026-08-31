<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['auth.login', 'profile.updated', 'user.created']),
            'subject_type' => User::class,
            'subject_id' => null,
            'ip_address' => fake()->ipv4(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
