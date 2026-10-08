<?php

namespace App\Enums;

enum EProposalStatus: string
{
    case Diajukan = 'DIAJUKAN';
    case PerluRevisi = 'PERLU_REVISI';
    case Terverifikasi = 'TERVERIFIKASI';
    case Published = 'PUBLISHED';

    public function label(): string
    {
        return match ($this) {
            self::Diajukan => 'Diajukan',
            self::PerluRevisi => 'Perlu Revisi',
            self::Terverifikasi => 'Terverifikasi',
            self::Published => 'Published',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Diajukan => 'badge-neutral',
            self::PerluRevisi => 'badge-warning',
            self::Terverifikasi => 'badge-info',
            self::Published => 'badge-success',
        };
    }

    public function isDownloadable(): bool
    {
        return in_array($this, [self::Terverifikasi, self::Published], true);
    }
}
