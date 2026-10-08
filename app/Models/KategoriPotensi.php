<?php

namespace App\Models;

use Database\Factories\KategoriPotensiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'kode', 'deskripsi', 'is_active'])]
class KategoriPotensi extends Model
{
    /** @use HasFactory<KategoriPotensiFactory> */
    use HasFactory;

    protected $table = 'kategori_potensi';

    /** @return HasMany<PotensiDesa, $this> */
    public function potentials(): HasMany
    {
        return $this->hasMany(PotensiDesa::class, 'kategori_potensi_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
