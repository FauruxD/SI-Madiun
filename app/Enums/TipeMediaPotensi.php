<?php

namespace App\Enums;

enum TipeMediaPotensi: string
{
    case Image = 'IMAGE';
    case Video = 'VIDEO';

    public function label(): string
    {
        return match ($this) {
            self::Image => 'Foto',
            self::Video => 'Video',
        };
    }
}
