<?php

namespace App\Models;

use Database\Factories\KeuanganBumdesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bumdes_id', 'tahun', 'modal_awal', 'omzet', 'shu', 'kontribusi_pades'])]
class KeuanganBumdes extends Model
{
    /** @use HasFactory<KeuanganBumdesFactory> */
    use HasFactory;

    protected $table = 'keuangan_bumdes';

    /** @return BelongsTo<Bumdes, $this> */
    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(Bumdes::class, 'bumdes_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'modal_awal' => 'decimal:2',
            'omzet' => 'decimal:2',
            'shu' => 'decimal:2',
            'kontribusi_pades' => 'decimal:2',
        ];
    }
}
