<?php

namespace App\Models;

use App\Enums\StatusKemitraan;
use Database\Factories\KemitraanBumdesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bumdes_id', 'nama_mitra', 'jenis_kemitraan', 'deskripsi', 'status', 'kebutuhan_investasi'])]
class KemitraanBumdes extends Model
{
    /** @use HasFactory<KemitraanBumdesFactory> */
    use HasFactory;

    protected $table = 'kemitraan_bumdes';

    /** @return BelongsTo<Bumdes, $this> */
    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(Bumdes::class, 'bumdes_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => StatusKemitraan::class, 'kebutuhan_investasi' => 'decimal:2'];
    }
}
