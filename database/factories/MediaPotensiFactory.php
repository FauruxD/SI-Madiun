<?php

namespace Database\Factories;

use App\Enums\TipeMediaPotensi;
use App\Models\MediaPotensi;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaPotensi>
 */
class MediaPotensiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'potensi_desa_id' => PotensiDesa::factory(),
            'tipe_media' => TipeMediaPotensi::Image,
            'judul' => fake()->optional()->sentence(3),
            'nama_file' => 'potensi.jpg',
            'file_path' => 'media/potensi/'.fake()->uuid().'/images/potensi.jpg',
            'media_url' => null,
            'mime_type' => 'image/jpeg',
            'file_size' => 2048,
            'urutan' => fake()->numberBetween(0, 20),
            'uploaded_by' => User::factory()->superAdmin(),
        ];
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipe_media' => TipeMediaPotensi::Video,
            'nama_file' => null,
            'file_path' => null,
            'media_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'mime_type' => null,
            'file_size' => null,
        ]);
    }
}
