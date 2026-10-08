<?php

namespace App\Models;

use App\Enums\KawasanStatus;
use App\UserRole;
use Database\Factories\KawasanPerdesaanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nama', 'deskripsi', 'tanggal_penetapan', 'status'])]
class KawasanPerdesaan extends Model
{
    /** @use HasFactory<KawasanPerdesaanFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'kawasan_perdesaan';

    /** @return BelongsToMany<Desa, $this> */
    public function desas(): BelongsToMany
    {
        return $this->belongsToMany(Desa::class, 'kawasan_desa', 'kawasan_id', 'desa_id')
            ->withPivot('tanggal_bergabung')
            ->withTimestamps();
    }

    /** @return BelongsToMany<KategoriKawasan, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(KategoriKawasan::class, 'kawasan_kategori', 'kawasan_id', 'kategori_kawasan_id')
            ->withTimestamps();
    }

    /** @return BelongsToMany<User, $this> */
    public function adminUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_kawasan', 'kawasan_id', 'user_id')
            ->withTimestamps();
    }

    /** @return HasMany<DokumenKawasan, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(DokumenKawasan::class, 'kawasan_id');
    }

    /** @return HasMany<TitikInformasi, $this> */
    public function informationPoints(): HasMany
    {
        return $this->hasMany(TitikInformasi::class, 'kawasan_id');
    }

    /**
     * @param  Builder<KawasanPerdesaan>  $query
     * @return Builder<KawasanPerdesaan>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->role === UserRole::AdminKawasan) {
            return $query->whereHas('adminUsers', fn (Builder $users) => $users->whereKey($user->getKey()));
        }

        return $query->whereIn($query->qualifyColumn('id'), []);
    }

    /**
     * @param  Builder<KawasanPerdesaan>  $query
     * @return Builder<KawasanPerdesaan>
     */
    public function scopeEstablished(Builder $query): Builder
    {
        return $query->where('status', KawasanStatus::Ditetapkan->value);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'geometri' => 'array',
            'tanggal_penetapan' => 'date',
            'status' => KawasanStatus::class,
        ];
    }
}
