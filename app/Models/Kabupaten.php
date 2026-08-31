<?php

namespace App\Models;

use Database\Factories\KabupatenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kode', 'nama'])]
class Kabupaten extends Model
{
    /** @use HasFactory<KabupatenFactory> */
    use HasFactory;

    protected $table = 'kabupaten';

    /** @return HasMany<Kecamatan, $this> */
    public function kecamatans(): HasMany
    {
        return $this->hasMany(Kecamatan::class);
    }
}
