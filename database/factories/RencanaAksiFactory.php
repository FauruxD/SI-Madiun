<?php

namespace Database\Factories;

use App\Enums\ActionPeriod;
use App\Models\Bumdes;
use App\Models\RencanaAksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RencanaAksi> */
class RencanaAksiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bumdes_id' => Bumdes::factory(),
            'tahun' => fake()->numberBetween(2024, (int) date('Y') + 1),
            'program' => fake()->sentence(4),
            'kegiatan' => fake()->sentence(6),
            'target_deskripsi' => fake()->optional()->sentence(),
            'target_nilai' => fake()->randomElement(['100.00', '250.00', '1000.00']),
            'target_satuan' => fake()->randomElement(['unit', 'peserta', 'kg']),
            'periode' => ActionPeriod::Monthly,
            'tanggal_mulai' => null,
            'tanggal_selesai' => null,
        ];
    }

    public function descriptive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'target_nilai' => null,
            'target_satuan' => null,
            'target_deskripsi' => 'Dokumen kerja sama ditandatangani.',
        ]);
    }
}
