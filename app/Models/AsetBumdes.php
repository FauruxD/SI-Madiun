<?php

namespace App\Models;

use Database\Factories\AsetBumdesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bumdes_id', 'nama', 'jenis', 'deskripsi', 'nilai_perkiraan', 'status_kepemilikan', 'kondisi'])]
class AsetBumdes extends Model
{
    /** @use HasFactory<AsetBumdesFactory> */
    use HasFactory;

    protected $table = 'aset_bumdes';

    /** @return BelongsTo<Bumdes, $this> */
    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(Bumdes::class, 'bumdes_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['nilai_perkiraan' => 'decimal:2'];
    }
}
