<?php

namespace App\Models;

use App\Enums\AchievementStatus;
use Database\Factories\RealisasiAksiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rencana_aksi_id', 'periode_ke', 'realisasi_nilai', 'catatan', 'tanggal_realisasi', 'persentase_capaian', 'status'])]
class RealisasiAksi extends Model
{
    /** @use HasFactory<RealisasiAksiFactory> */
    use HasFactory;

    protected $table = 'realisasi_aksi';

    /** @return BelongsTo<RencanaAksi, $this> */
    public function actionPlan(): BelongsTo
    {
        return $this->belongsTo(RencanaAksi::class, 'rencana_aksi_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'periode_ke' => 'integer',
            'realisasi_nilai' => 'decimal:2',
            'persentase_capaian' => 'decimal:2',
            'tanggal_realisasi' => 'date',
            'status' => AchievementStatus::class,
        ];
    }
}
