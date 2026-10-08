<?php

namespace App\Models;

use Database\Factories\ProdukLayananFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['unit_usaha_id', 'nama', 'kategori', 'deskripsi', 'harga', 'satuan', 'foto_path', 'is_active'])]
class ProdukLayanan extends Model
{
    /** @use HasFactory<ProdukLayananFactory> */
    use HasFactory;

    protected $table = 'produk_layanan';

    /** @return BelongsTo<UnitUsaha, $this> */
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['harga' => 'decimal:2', 'is_active' => 'boolean'];
    }
}
