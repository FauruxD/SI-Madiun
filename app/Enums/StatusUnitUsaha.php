<?php

namespace App\Enums;

enum StatusUnitUsaha: string
{
    case Aktif = 'AKTIF';
    case Nonaktif = 'NONAKTIF';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::Nonaktif => 'Nonaktif',
        };
    }
}
