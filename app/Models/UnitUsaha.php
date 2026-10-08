<?php

namespace App\Models;

use App\Enums\StatusUnitUsaha;
use Database\Factories\UnitUsahaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['bumdes_id', 'nama', 'deskripsi', 'status'])]
class UnitUsaha extends Model
{
    /** @use HasFactory<UnitUsahaFactory> */
    use HasFactory;

    protected $table = 'unit_usaha';

    /** @return BelongsTo<Bumdes, $this> */
    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(Bumdes::class, 'bumdes_id');
    }

    /** @return HasMany<ProdukLayanan, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(ProdukLayanan::class, 'unit_usaha_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => StatusUnitUsaha::class];
    }
}
