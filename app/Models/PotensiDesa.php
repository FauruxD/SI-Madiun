<?php

namespace App\Models;

use App\Enums\PotensiStatus;
use App\Enums\TipeMediaPotensi;
use App\UserRole;
use Database\Factories\PotensiDesaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['desa_id', 'kategori_potensi_id', 'bumdes_id', 'judul', 'deskripsi', 'data_pendukung', 'kebutuhan_investasi', 'status', 'published_at'])]
class PotensiDesa extends Model
{
    /** @use HasFactory<PotensiDesaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'potensi_desa';

    /** @return BelongsTo<Desa, $this> */
    public function village(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    /** @return BelongsTo<KategoriPotensi, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(KategoriPotensi::class, 'kategori_potensi_id');
    }

    /** @return BelongsTo<Bumdes, $this> */
    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(Bumdes::class, 'bumdes_id');
    }

    /** @return HasMany<MediaPotensi, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(MediaPotensi::class, 'potensi_desa_id');
    }

    /** @return HasOne<EProposal, $this> */
    public function eproposal(): HasOne
    {
        return $this->hasOne(EProposal::class, 'potensi_desa_id');
    }

    /** @return HasOne<MediaPotensi, $this> */
    public function coverImage(): HasOne
    {
        return $this->hasOne(MediaPotensi::class, 'potensi_desa_id')
            ->ofMany(
                ['urutan' => 'min', 'id' => 'min'],
                fn (Builder $query) => $query->where('tipe_media', TipeMediaPotensi::Image->value),
            );
    }

    /**
     * @param  Builder<PotensiDesa>  $query
     * @return Builder<PotensiDesa>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->role === UserRole::AdminDesa) {
            return $query->whereHas('village.users', fn (Builder $users) => $users->whereKey($user->getKey()));
        }

        if ($user->role === UserRole::AdminKawasan) {
            return $query->whereHas(
                'village.kawasans.adminUsers',
                fn (Builder $users) => $users->whereKey($user->getKey()),
            );
        }

        return $query->whereIn($query->qualifyColumn('id'), []);
    }

    /**
     * @param  Builder<PotensiDesa>  $query
     * @return Builder<PotensiDesa>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PotensiStatus::Published->value);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'geometri' => 'array',
            'kebutuhan_investasi' => 'decimal:2',
            'status' => PotensiStatus::class,
            'published_at' => 'datetime',
        ];
    }
}
