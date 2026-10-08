<?php

namespace App\Actions;

use App\Models\RealisasiAksi;
use App\Models\RencanaAksi;

class SaveActionRealization
{
    public function __construct(private ActionAchievementCalculator $calculator) {}

    /** @param array<string, mixed> $attributes */
    public function create(RencanaAksi $actionPlan, array $attributes): RealisasiAksi
    {
        $attributes['persentase_capaian'] = $this->calculator->percentage(
            $actionPlan->target_nilai,
            $attributes['realisasi_nilai'],
        );

        return $actionPlan->realizations()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function update(RencanaAksi $actionPlan, RealisasiAksi $realization, array $attributes): RealisasiAksi
    {
        $attributes['persentase_capaian'] = $this->calculator->percentage(
            $actionPlan->target_nilai,
            $attributes['realisasi_nilai'],
        );
        $realization->update($attributes);

        return $realization->refresh();
    }
}
