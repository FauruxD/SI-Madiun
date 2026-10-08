<?php

namespace Database\Factories;

use App\Actions\ActionAchievementCalculator;
use App\Enums\AchievementStatus;
use App\Models\RealisasiAksi;
use App\Models\RencanaAksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RealisasiAksi> */
class RealisasiAksiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rencana_aksi_id' => RencanaAksi::factory(),
            'periode_ke' => 1,
            'realisasi_nilai' => '25.00',
            'catatan' => fake()->optional()->sentence(),
            'tanggal_realisasi' => fake()->date(),
            'persentase_capaian' => function (array $attributes): ?string {
                $plan = RencanaAksi::query()->find($attributes['rencana_aksi_id']);

                return app(ActionAchievementCalculator::class)->percentage(
                    $plan?->target_nilai,
                    $attributes['realisasi_nilai'],
                );
            },
            'status' => AchievementStatus::InProgress,
        ];
    }
}
