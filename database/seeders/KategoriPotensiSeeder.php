<?php

namespace Database\Seeders;

use App\Models\KategoriPotensi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriPotensiSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['kode' => 'PERTANIAN', 'nama' => 'Pertanian'],
            ['kode' => 'PARIWISATA', 'nama' => 'Pariwisata'],
            ['kode' => 'INDUSTRI_UMKM', 'nama' => 'Industri / UMKM'],
        ] as $category) {
            KategoriPotensi::query()->updateOrCreate(
                ['kode' => $category['kode']],
                [...$category, 'deskripsi' => null, 'is_active' => true],
            );
        }
    }
}
