<?php

namespace App\Models;

use App\Enums\JenisBumdes;
use App\UserRole;
use Database\Factories\BumdesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nama', 'jenis', 'nomor_registrasi', 'tahun_berdiri', 'status_hukum', 'alamat', 'telepon', 'email', 'deskripsi'])]
class Bumdes extends Model
{
    /** @use HasFactory<BumdesFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'bumdes';

    /** @return HasOne<WebsiteBumdes, $this> */
    public function website(): HasOne
    {
        return $this->hasOne(WebsiteBumdes::class, 'bumdes_id');
    }

    /** @return BelongsToMany<Desa, $this> */
    public function desas(): BelongsToMany
    {
        return $this->belongsToMany(Desa::class, 'bumdes_desa', 'bumdes_id', 'desa_id')
            ->withPivot('is_desa_utama')
            ->withTimestamps();
    }

    /** @return HasMany<DokumenBumdes, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(DokumenBumdes::class, 'bumdes_id');
    }

    /** @return HasMany<PengurusBumdes, $this> */
    public function managers(): HasMany
    {
        return $this->hasMany(PengurusBumdes::class, 'bumdes_id');
    }

    /** @return HasMany<UnitUsaha, $this> */
    public function businessUnits(): HasMany
    {
        return $this->hasMany(UnitUsaha::class, 'bumdes_id');
    }

    /** @return HasMany<AsetBumdes, $this> */
    public function assets(): HasMany
    {
        return $this->hasMany(AsetBumdes::class, 'bumdes_id');
    }

    /** @return HasMany<KeuanganBumdes, $this> */
    public function financialSummaries(): HasMany
    {
        return $this->hasMany(KeuanganBumdes::class, 'bumdes_id');
    }

    /** @return HasMany<KemitraanBumdes, $this> */
    public function partnerships(): HasMany
    {
        return $this->hasMany(KemitraanBumdes::class, 'bumdes_id');
    }

    /** @return HasMany<GaleriBumdes, $this> */
    public function gallery(): HasMany
    {
        return $this->hasMany(GaleriBumdes::class, 'bumdes_id');
    }

    /** @return HasMany<RencanaAksi, $this> */
    public function actionPlans(): HasMany
    {
        return $this->hasMany(RencanaAksi::class, 'bumdes_id');
    }

    /** @return HasMany<PotensiDesa, $this> */
    public function potentials(): HasMany
    {
        return $this->hasMany(PotensiDesa::class, 'bumdes_id');
    }

    /**
     * @param  Builder<Bumdes>  $query
     * @return Builder<Bumdes>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->role === UserRole::AdminDesa) {
            return $query->whereHas('desas.users', fn (Builder $users) => $users->whereKey($user->getKey()));
        }

        if ($user->role === UserRole::AdminKawasan) {
            return $query->whereHas(
                'desas.kawasans.adminUsers',
                fn (Builder $users) => $users->whereKey($user->getKey()),
            );
        }

        return $query->whereIn($query->qualifyColumn('id'), []);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['jenis' => JenisBumdes::class, 'tahun_berdiri' => 'integer', 'geometri' => 'array'];
    }
}
