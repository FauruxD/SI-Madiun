<?php

namespace App\Enums;

enum AchievementStatus: string
{
    case NotStarted = 'NOT_STARTED';
    case InProgress = 'IN_PROGRESS';
    case Completed = 'COMPLETED';
    case Delayed = 'DELAYED';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Belum Mulai',
            self::InProgress => 'Berjalan',
            self::Completed => 'Selesai',
            self::Delayed => 'Tertunda',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Completed => 'badge-success',
            self::Delayed => 'badge-danger',
            self::InProgress => 'badge-warning',
            self::NotStarted => 'badge-neutral',
        };
    }
}
