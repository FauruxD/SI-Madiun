<?php

namespace App\Models;

use Database\Factories\KategoriKawasanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nama', 'kode', 'deskripsi', 'is_active'])]
class KategoriKawasan extends Model
{
    /** @use HasFactory<KategoriKawasanFactory> */
    use HasFactory;

    protected $table = 'kategori_kawasan';

    /** @return BelongsToMany<KawasanPerdesaan, $this> */
    public function kawasans(): BelongsToMany
    {
        return $this->belongsToMany(KawasanPerdesaan::class, 'kawasan_kategori', 'kategori_kawasan_id', 'kawasan_id')
            ->withTimestamps();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
