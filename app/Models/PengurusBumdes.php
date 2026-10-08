<?php

namespace App\Models;

use Database\Factories\PengurusBumdesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bumdes_id', 'nama', 'jabatan', 'foto_path', 'nomor_telepon', 'email', 'urutan', 'is_active'])]
class PengurusBumdes extends Model
{
    /** @use HasFactory<PengurusBumdesFactory> */
    use HasFactory;

    protected $table = 'pengurus_bumdes';

    /** @return BelongsTo<Bumdes, $this> */
    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(Bumdes::class, 'bumdes_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['urutan' => 'integer', 'is_active' => 'boolean'];
    }
}
