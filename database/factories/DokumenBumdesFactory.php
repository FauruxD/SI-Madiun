<?php

namespace Database\Factories;

use App\Enums\JenisDokumenBumdes;
use App\Models\Bumdes;
use App\Models\DokumenBumdes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DokumenBumdes>
 */
class DokumenBumdesFactory extends Factory
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
            'jenis_dokumen' => JenisDokumenBumdes::Ad,
            'nomor_dokumen' => fake()->optional()->numerify('##/AD/####'),
            'tanggal_dokumen' => fake()->optional()->date(),
            'nama_file' => 'dokumen-ad.pdf',
            'file_path' => 'documents/bumdes/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'ringkasan_maksud_tujuan' => fake()->optional()->sentence(),
            'ringkasan_modal_awal' => fake()->optional()->randomFloat(2, 1000000, 500000000),
            'ringkasan_pembagian_shu' => fake()->optional()->sentence(),
            'uploaded_by' => User::factory()->superAdmin(),
        ];
    }
}
