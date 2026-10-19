# Sistem Informasi Kawasan Perdesaan Kabupaten Madiun

Sistem Informasi Kawasan Perdesaan Kabupaten Madiun adalah platform berbasis web untuk mendukung tata kelola kawasan perdesaan, pengelolaan BUMDes/BUMDesma, pemetaan potensi desa, pengajuan e-proposal, serta penyediaan peta interaktif dan portal informasi publik bagi masyarakat.

---

## Fitur Utama

- **Portal Informasi Publik**: Akses informasi profil kawasan, potensi desa, produk BUMDes, dan peta geospasial bagi masyarakat umum.
- **Pengelolaan Kawasan Perdesaan**: Manajemen data kawasan, batas wilayah, dokumen legalitas, dan penetapan desa anggota.
- **Manajemen BUMDes & BUMDesma**: Pencatatan kelembagaan, perizinan, unit usaha, aset, laporan keuangan, dan peluang kemitraan.
- **Katalog Potensi Desa**: Publikasi potensi komoditas, pariwisata, kerajinan, dan peluang investasi lokal.
- **E-Proposal**: Alur pengajuan, verifikasi, disposisi, dan pelacakan proposal pembangunan kawasan perdesaan secara digital.
- **Peta Interaktif (Geospasial)**: Visualisasi sebaran kawasan, desa, dan potensi daerah berbasis peta digital (GeoJSON).
- **Dashboard & Analitik**: Rekapitulasi data berbasis peran (Super Admin, Admin Kawasan, dan Admin Desa) dengan activity logging.

---

## Teknologi yang Digunakan

- **Backend**: PHP 8.3+ & Laravel
- **Frontend**: Blade Templating, Tailwind CSS, Vite
- **Database**: MySQL 5.7+ / MariaDB
- **Pemetaan**: Leaflet.js / OpenStreetMap (GeoJSON)

---

## Panduan Instalasi

### 1. Prasyarat Sistem
Pastikan perangkat Anda telah terpasang:
- PHP >= 8.3 dengan ekstensi pendukung (BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML)
- Composer
- MySQL atau MariaDB
- Node.js & npm

### 2. Kloning Repository & Instalasi Dependency
```bash
git clone https://github.com/FauruxD/SI-Madiun.git
cd SI-Madiun

# Install dependency backend
composer install

# Salin konfigurasi environment
cp .env.example .env
php artisan key:generate
```

### 3. Konfigurasi Database & Environment
Buka file `.env` lalu sesuaikan konfigurasi database Anda:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=si_madiun
DB_USERNAME=root
DB_PASSWORD=
```

Konfigurasi penyimpanan berkas dokumen dan media (opsional):
```dotenv
KAWASAN_DOCUMENT_DISK=local
KAWASAN_DOCUMENT_MAX_SIZE_KB=10240
```

### 4. Migrasi & Build Asset
```bash
# Jalankan migrasi database
php artisan migrate

# Install dependency frontend dan build asset
npm install
npm run build
```

### 5. Menjalankan Server Lokal
```bash
php artisan serve
```
Aplikasi dapat diakses melalui peramban di `http://127.0.0.1:8000`.

---

## Hak Akses (Role-Based Access Control)

Sistem ini mendukung pengelolaan bertingkat sesuai hierarki kewenangan:
1. **Super Admin (DPMD)**: Mengelola master wilayah, manajemen pengguna, verifikasi akhir usulan/e-proposal, dan pengaturan global sistem.
2. **Admin Kawasan**: Mengelola administrasi kawasan, koordinasi desa anggota, serta perencanaan program kawasan.
3. **Admin Desa / BUMDes**: Mengelola data desa, inventaris BUMDes, produk unggulan, dan pengajuan usulan.

---

## Pengujian & Standar Kode

Untuk menjalankan pengujian otomatis dan perapian format kode:

```bash
# Menjalankan automated test
php artisan test

# Format kode sesuai standar Laravel Pint
vendor/bin/pint
```
