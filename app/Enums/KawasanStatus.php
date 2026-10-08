<?php

namespace App\Enums;

enum KawasanStatus: string
{
    case Draft = 'DRAFT';
    case Ditetapkan = 'DITETAPKAN';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Ditetapkan => 'Ditetapkan',
        };
    }
}
