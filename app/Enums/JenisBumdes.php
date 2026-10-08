<?php

namespace App\Enums;

enum JenisBumdes: string
{
    case Bumdes = 'BUMDES';
    case Bumdesma = 'BUMDESMA';

    public function label(): string
    {
        return match ($this) {
            self::Bumdes => 'BUMDes',
            self::Bumdesma => 'BUMDesma',
        };
    }
}
