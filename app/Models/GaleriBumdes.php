<?php

namespace App\Models;

use App\Enums\TipeMediaBumdes;
use Database\Factories\GaleriBumdesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bumdes_id', 'tipe_media', 'judul', 'nama_file', 'file_path', 'mime_type', 'file_size', 'urutan', 'uploaded_by'])]
class GaleriBumdes extends Model
{
    /** @use HasFactory<GaleriBumdesFactory> */
    use HasFactory;

    protected $table = 'galeri_bumdes';

    /** @return BelongsTo<Bumdes, $this> */
    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(Bumdes::class, 'bumdes_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['tipe_media' => TipeMediaBumdes::class, 'urutan' => 'integer'];
    }
}
