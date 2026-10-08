<?php

namespace App\Models;

use App\Enums\JenisDokumenBumdes;
use Database\Factories\DokumenBumdesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bumdes_id', 'jenis_dokumen', 'nomor_dokumen', 'tanggal_dokumen', 'nama_file', 'file_path', 'mime_type', 'file_size', 'ringkasan_maksud_tujuan', 'ringkasan_modal_awal', 'ringkasan_pembagian_shu', 'uploaded_by'])]
class DokumenBumdes extends Model
{
    /** @use HasFactory<DokumenBumdesFactory> */
    use HasFactory;

    protected $table = 'dokumen_bumdes';

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
        return ['jenis_dokumen' => JenisDokumenBumdes::class, 'tanggal_dokumen' => 'date', 'ringkasan_modal_awal' => 'decimal:2'];
    }
}
