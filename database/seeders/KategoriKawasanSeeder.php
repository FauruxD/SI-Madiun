<?php

namespace Database\Seeders;

use App\Models\KategoriKawasan;
use Illuminate\Database\Seeder;

class KategoriKawasanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['kode' => 'AGROPOLITAN', 'nama' => 'Agropolitan'],
            ['kode' => 'MINAPOLITAN', 'nama' => 'Minapolitan'],
            ['kode' => 'AGROWISATA', 'nama' => 'Agrowisata'],
            ['kode' => 'AGROINDUSTRI', 'nama' => 'Agroindustri'],
        ] as $category) {
            KategoriKawasan::query()->updateOrCreate(
                ['kode' => $category['kode']],
                [...$category, 'deskripsi' => null, 'is_active' => true],
            );
        }
    }
}
