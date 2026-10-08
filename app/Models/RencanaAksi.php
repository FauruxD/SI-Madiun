<?php

namespace App\Models;

use App\Enums\ActionPeriod;
use Database\Factories\RencanaAksiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['bumdes_id', 'tahun', 'program', 'kegiatan', 'target_deskripsi', 'target_nilai', 'target_satuan', 'periode', 'tanggal_mulai', 'tanggal_selesai'])]
class RencanaAksi extends Model
{
    /** @use HasFactory<RencanaAksiFactory> */
    use HasFactory;

    protected $table = 'rencana_aksi';

    /** @return BelongsTo<Bumdes, $this> */
    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(Bumdes::class, 'bumdes_id');
    }

    /** @return HasMany<RealisasiAksi, $this> */
    public function realizations(): HasMany
    {
        return $this->hasMany(RealisasiAksi::class, 'rencana_aksi_id');
    }

    /**
     * @param  Builder<RencanaAksi>  $query
     * @return Builder<RencanaAksi>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('bumdes', fn (Builder $bumdes) => $bumdes->visibleTo($user));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'target_nilai' => 'decimal:2',
            'periode' => ActionPeriod::class,
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }
}
