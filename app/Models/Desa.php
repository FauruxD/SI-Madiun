<?php

namespace App\Models;

use Database\Factories\DesaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kecamatan_id', 'kode', 'nama'])]
class Desa extends Model
{
    /** @use HasFactory<DesaFactory> */
    use HasFactory;

    protected $table = 'desa';

    /** @return BelongsTo<Kecamatan, $this> */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_desa')->withTimestamps();
    }

    /** @return BelongsToMany<KawasanPerdesaan, $this> */
    public function kawasans(): BelongsToMany
    {
        return $this->belongsToMany(KawasanPerdesaan::class, 'kawasan_desa', 'desa_id', 'kawasan_id')
            ->withPivot('tanggal_bergabung')
            ->withTimestamps();
    }

    /** @return BelongsToMany<Bumdes, $this> */
    public function bumdes(): BelongsToMany
    {
        return $this->belongsToMany(Bumdes::class, 'bumdes_desa', 'desa_id', 'bumdes_id')
            ->withPivot('is_desa_utama')
            ->withTimestamps();
    }

    /** @return HasMany<PotensiDesa, $this> */
    public function potentials(): HasMany
    {
        return $this->hasMany(PotensiDesa::class, 'desa_id');
    }

    /**
     * @param  Builder<Desa>  $query
     * @return Builder<Desa>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereHas('users', fn (Builder $users) => $users->whereKey($user->getKey()));
    }
}
