<?php

namespace App\Enums;

enum JenisDokumenKawasan: string
{
    case SkBupati = 'SK_BUPATI';
    case DokumenTkpkp = 'DOKUMEN_TKPKP';

    public function label(): string
    {
        return match ($this) {
            self::SkBupati => 'SK Bupati',
            self::DokumenTkpkp => 'Dokumen TKPKP',
        };
    }
}
