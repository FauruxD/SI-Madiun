<?php

namespace App\Enums;

enum TipeMediaBumdes: string
{
    case Image = 'IMAGE';

    public function label(): string
    {
        return 'Foto';
    }
}
