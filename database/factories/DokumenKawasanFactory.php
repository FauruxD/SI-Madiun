<?php

namespace Database\Factories;

use App\Enums\JenisDokumenKawasan;
use App\Models\DokumenKawasan;
use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<DokumenKawasan> */
class DokumenKawasanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kawasan_id' => KawasanPerdesaan::factory(),
            'jenis_dokumen' => JenisDokumenKawasan::SkBupati,
            'nomor_dokumen' => fake()->optional()->numerify('188.45/###/2026'),
            'nama_file' => 'dasar-hukum.pdf',
            'file_path' => 'kawasan-documents/'.Str::uuid().'.pdf',
            'tanggal_dokumen' => fake()->optional()->date(),
            'mime_type' => 'application/pdf',
            'file_size' => 128,
            'uploaded_by' => User::factory()->superAdmin(),
        ];
    }
}
