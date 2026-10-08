<?php

namespace App\Models;

use App\Enums\TipeMediaPotensi;
use Database\Factories\MediaPotensiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['potensi_desa_id', 'tipe_media', 'judul', 'nama_file', 'file_path', 'media_url', 'mime_type', 'file_size', 'urutan', 'uploaded_by'])]
class MediaPotensi extends Model
{
    /** @use HasFactory<MediaPotensiFactory> */
    use HasFactory;

    protected $table = 'media_potensi';

    /** @return BelongsTo<PotensiDesa, $this> */
    public function potential(): BelongsTo
    {
        return $this->belongsTo(PotensiDesa::class, 'potensi_desa_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tipe_media' => TipeMediaPotensi::class,
            'file_size' => 'integer',
            'urutan' => 'integer',
        ];
    }
}
