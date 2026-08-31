<?php

namespace App;

enum UserRole: string
{
    case SuperAdmin = 'SUPER_ADMIN';
    case AdminKawasan = 'ADMIN_KAWASAN';
    case AdminDesa = 'ADMIN_DESA';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::AdminKawasan => 'Admin Kawasan',
            self::AdminDesa => 'Admin Desa',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Pengelola sistem',
            self::AdminKawasan => 'Pengelola kawasan',
            self::AdminDesa => 'Pengelola desa',
        };
    }
}
