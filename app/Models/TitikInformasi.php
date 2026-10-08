<?php

namespace App\Models;

use Database\Factories\TitikInformasiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nama', 'jenis', 'deskripsi', 'geometri', 'is_active'])]
class TitikInformasi extends Model
{
    /** @use HasFactory<TitikInformasiFactory> */
    use HasFactory;

    public const TYPES = [
        'pertemuan' => 'Tempat pertemuan',
        'parkir' => 'Area parkir',
        'kesehatan' => 'Fasilitas kesehatan',
        'akses_jalan' => 'Akses jalan utama',
        'lainnya' => 'Informasi lainnya',
    ];

    protected $table = 'titik_informasi';

    /** @return BelongsTo<KawasanPerdesaan, $this> */
    public function kawasan(): BelongsTo
    {
        return $this->belongsTo(KawasanPerdesaan::class, 'kawasan_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['geometri' => 'array', 'is_active' => 'boolean'];
    }
}
