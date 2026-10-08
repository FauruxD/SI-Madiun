<?php

namespace App\Enums;

enum ActionPeriod: string
{
    case Monthly = 'MONTHLY';
    case Quarterly = 'QUARTERLY';
    case Yearly = 'YEARLY';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Bulanan',
            self::Quarterly => 'Triwulanan',
            self::Yearly => 'Tahunan',
        };
    }

    public function maximumPeriods(): int
    {
        return match ($this) {
            self::Monthly => 12,
            self::Quarterly => 4,
            self::Yearly => 1,
        };
    }

    public function periodLabel(int $period): string
    {
        if ($period < 1 || $period > $this->maximumPeriods()) {
            throw new \InvalidArgumentException('Nomor periode tidak sesuai dengan frekuensi rencana aksi.');
        }

        return match ($this) {
            self::Monthly => [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ][$period],
            self::Quarterly => 'Triwulan '.$period,
            self::Yearly => 'Tahunan',
        };
    }
}
