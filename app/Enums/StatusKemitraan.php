<?php

namespace App\Enums;

enum StatusKemitraan: string
{
    case Berjalan = 'BERJALAN';
    case Dibutuhkan = 'DIBUTUHKAN';

    public function label(): string
    {
        return match ($this) {
            self::Berjalan => 'Mitra berjalan',
            self::Dibutuhkan => 'Mitra/investasi dibutuhkan',
        };
    }
}
