<?php

namespace App\Models;

use App\Enums\JenisDokumenKawasan;
use Database\Factories\DokumenKawasanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['kawasan_id', 'jenis_dokumen', 'nomor_dokumen', 'nama_file', 'file_path', 'tanggal_dokumen', 'mime_type', 'file_size', 'uploaded_by'])]
class DokumenKawasan extends Model
{
    /** @use HasFactory<DokumenKawasanFactory> */
    use HasFactory;

    protected $table = 'dokumen_kawasan';

    /** @return BelongsTo<KawasanPerdesaan, $this> */
    public function kawasan(): BelongsTo
    {
        return $this->belongsTo(KawasanPerdesaan::class, 'kawasan_id');
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
            'jenis_dokumen' => JenisDokumenKawasan::class,
            'tanggal_dokumen' => 'date',
            'file_size' => 'integer',
        ];
    }
}
