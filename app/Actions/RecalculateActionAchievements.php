<?php

namespace App\Actions;

use App\Models\RencanaAksi;

class RecalculateActionAchievements
{
    public function __construct(private ActionAchievementCalculator $calculator) {}

    public function execute(RencanaAksi $actionPlan): void
    {
        $actionPlan->realizations()->each(function ($realization) use ($actionPlan): void {
            $realization->update([
                'persentase_capaian' => $this->calculator->percentage(
                    $actionPlan->target_nilai,
                    $realization->realisasi_nilai,
                ),
            ]);
        });
    }
}
