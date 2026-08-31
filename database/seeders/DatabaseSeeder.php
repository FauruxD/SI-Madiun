<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $kabupaten = Kabupaten::query()->updateOrCreate(
            ['kode' => '35.19'],
            ['nama' => 'Kabupaten Madiun'],
        );

        $kecamatan = Kecamatan::query()->updateOrCreate(
            ['kode' => '35.19.13'],
            ['kabupaten_id' => $kabupaten->id, 'nama' => 'Wungu'],
        );

        $desa = collect([
            ['kode' => '35.19.13.2005', 'nama' => 'Brumbun'],
            ['kode' => '35.19.13.2006', 'nama' => 'Kresek'],
            ['kode' => '35.19.13.2007', 'nama' => 'Mojopurno'],
        ])->map(fn (array $data): Desa => Desa::query()->updateOrCreate(
            ['kode' => $data['kode']],
            ['kecamatan_id' => $kecamatan->id, 'nama' => $data['nama']],
        ));

        $users = collect([
            [
                'name' => 'Super Admin Demo',
                'username' => 'superadmin',
                'email' => 'superadmin@si-madiun.test',
                'role' => UserRole::SuperAdmin,
            ],
            [
                'name' => 'Admin Kawasan Demo',
                'username' => 'adminkawasan',
                'email' => 'adminkawasan@si-madiun.test',
                'role' => UserRole::AdminKawasan,
            ],
            [
                'name' => 'Admin Desa Demo',
                'username' => 'admindesa',
                'email' => 'admindesa@si-madiun.test',
                'role' => UserRole::AdminDesa,
            ],
        ])->map(fn (array $data): User => User::query()->updateOrCreate(
            ['email' => $data['email']],
            [...$data, 'phone' => null, 'is_active' => true, 'password' => 'DemoMadiun!2026'],
        ));

        $users->firstWhere('role', UserRole::AdminDesa)?->desas()->sync([
            $desa->firstOrFail()->id,
        ]);
    }
}
