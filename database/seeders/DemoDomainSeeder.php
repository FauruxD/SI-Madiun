<?php

namespace Database\Seeders;

use App\Enums\EProposalStatus;
use App\Enums\KawasanStatus;
use App\Enums\PotensiStatus;
use App\Enums\StatusKemitraan;
use App\Enums\StatusUnitUsaha;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\EProposal;
use App\Models\KategoriKawasan;
use App\Models\KategoriPotensi;
use App\Models\KawasanPerdesaan;
use App\Models\KemitraanBumdes;
use App\Models\PengurusBumdes;
use App\Models\PotensiDesa;
use App\Models\ProdukLayanan;
use App\Models\UnitUsaha;
use App\Models\User;
use App\Models\WebsiteBumdes;
use App\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoDomainSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $desas = Desa::query()->whereIn('kode', ['35.19.13.2005', '35.19.13.2006', '35.19.13.2007'])->orderBy('kode')->get();
        $primaryVillage = $desas->firstOrFail();

        $kawasan = KawasanPerdesaan::query()->updateOrCreate(
            ['nama' => 'DEMO - Kawasan Lereng Wilis'],
            [
                'deskripsi' => 'Contoh kawasan kolaborasi desa untuk agrowisata, pertanian, dan penguatan usaha lokal.',
                'tanggal_penetapan' => '2025-05-12',
                'status' => KawasanStatus::Ditetapkan,
            ],
        );
        $kawasan->desas()->sync($desas->mapWithKeys(fn (Desa $desa): array => [
            $desa->id => ['tanggal_bergabung' => '2025-05-12'],
        ])->all());
        $kawasan->categories()->sync(KategoriKawasan::query()->whereIn('kode', ['AGROPOLITAN', 'AGROWISATA'])->pluck('id'));
        $areaAdmin = User::query()->where('role', UserRole::AdminKawasan)->first();
        if ($areaAdmin !== null) {
            $kawasan->adminUsers()->syncWithoutDetaching([$areaAdmin->id]);
        }

        $bumdes = Bumdes::query()->updateOrCreate(
            ['nama' => 'DEMO - BUMDes Margo Makmur'],
            [
                'nomor_registrasi' => 'DEMO-BUMDES-001',
                'tahun_berdiri' => 2019,
                'status_hukum' => 'Perdes pendirian',
                'alamat' => 'Desa Brumbun, Kecamatan Wungu, Kabupaten Madiun',
                'telepon' => null,
                'email' => null,
                'deskripsi' => 'Contoh profil usaha desa yang mengelola hasil pertanian dan produk olahan lokal.',
            ],
        );
        $bumdes->desas()->sync([$primaryVillage->id => ['is_desa_utama' => true]]);
        $website = WebsiteBumdes::query()->updateOrCreate(
            ['bumdes_id' => $bumdes->id],
            ['subdomain' => 'demo-margo-makmur', 'custom_domain' => null, 'is_active' => true, 'activated_at' => '2026-01-10 08:00:00'],
        );

        PengurusBumdes::query()->updateOrCreate(
            ['bumdes_id' => $bumdes->id, 'nama' => 'Siti Rahayu'],
            ['jabatan' => 'Direktur', 'urutan' => 1, 'is_active' => true, 'foto_path' => null, 'nomor_telepon' => null, 'email' => null],
        );
        $unit = UnitUsaha::query()->updateOrCreate(
            ['bumdes_id' => $bumdes->id, 'nama' => 'Unit Olahan Pangan'],
            ['deskripsi' => 'Mengolah komoditas desa menjadi produk siap pasar.', 'status' => StatusUnitUsaha::Aktif],
        );
        ProdukLayanan::query()->updateOrCreate(
            ['unit_usaha_id' => $unit->id, 'nama' => 'Kopi Bubuk Lereng Wilis'],
            ['kategori' => 'Produk olahan', 'deskripsi' => 'Kopi bubuk contoh dari komoditas lokal lereng Wilis.', 'harga' => 45000, 'satuan' => 'kemasan', 'foto_path' => null, 'is_active' => true],
        );
        KemitraanBumdes::query()->updateOrCreate(
            ['bumdes_id' => $bumdes->id, 'jenis_kemitraan' => 'Distribusi produk olahan'],
            ['nama_mitra' => null, 'deskripsi' => 'Membuka kerja sama distribusi dan pengembangan pasar regional.', 'status' => StatusKemitraan::Dibutuhkan, 'kebutuhan_investasi' => 150000000],
        );

        $potential = PotensiDesa::query()->updateOrCreate(
            ['judul' => 'DEMO - Kopi Lereng Wilis'],
            [
                'desa_id' => $primaryVillage->id,
                'kategori_potensi_id' => KategoriPotensi::query()->where('kode', 'PERTANIAN')->valueOrFail('id'),
                'bumdes_id' => $bumdes->id,
                'deskripsi' => 'Contoh potensi kopi lokal dengan peluang penguatan pengolahan, kemasan, dan distribusi.',
                'data_pendukung' => 'Data ini hanya untuk demonstrasi alur aplikasi dan bukan data statistik resmi.',
                'kebutuhan_investasi' => 150000000,
                'status' => PotensiStatus::Published,
                'published_at' => '2026-01-15 08:00:00',
            ],
        );
        $villageAdmin = User::query()->where('role', UserRole::AdminDesa)->first();
        EProposal::query()->updateOrCreate(
            ['potensi_desa_id' => $potential->id],
            [
                'submitted_by' => $villageAdmin?->id,
                'nomor_proposal' => 'DEMO/EP/001/2026',
                'status' => EProposalStatus::Published,
                'catatan_pengaju' => 'Proposal contoh untuk demonstrasi portal publik.',
                'submitted_at' => '2026-01-12 08:00:00',
                'verified_at' => '2026-01-14 08:00:00',
                'published_at' => '2026-01-15 08:00:00',
            ],
        );

        $website->touch();
    }
}
