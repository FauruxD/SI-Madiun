<?php

namespace App\Enums;

enum JenisDokumenBumdes: string
{
    case Ad = 'AD';
    case Art = 'ART';
    case Perdes = 'PERDES';
    case Perkades = 'PERKADES';

    public function label(): string
    {
        return match ($this) {
            self::Ad => 'Anggaran Dasar (AD)',
            self::Art => 'Anggaran Rumah Tangga (ART)',
            self::Perdes => 'Peraturan Desa (Perdes)',
            self::Perkades => 'Peraturan Kepala Desa (Perkades)',
        };
    }
}
