<?php

namespace Database\Factories;

use App\Enums\TipeMediaBumdes;
use App\Models\Bumdes;
use App\Models\GaleriBumdes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GaleriBumdes>
 */
class GaleriBumdesFactory extends Factory
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
            'tipe_media' => TipeMediaBumdes::Image,
            'judul' => fake()->optional()->sentence(3),
            'nama_file' => 'galeri.jpg',
            'file_path' => 'images/bumdes/'.fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 2048,
            'urutan' => fake()->numberBetween(0, 20),
            'uploaded_by' => User::factory()->superAdmin(),
        ];
    }
}
