<?php

namespace App\Enums;

enum PotensiStatus: string
{
    case Draft = 'DRAFT';
    case Published = 'PUBLISHED';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Published => 'Published',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'badge-neutral',
            self::Published => 'badge-success',
        };
    }
}
