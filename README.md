# SI Kawasan Perdesaan Kabupaten Madiun

Aplikasi Laravel untuk pengelolaan kawasan perdesaan, BUMDes/BUMDesma, potensi desa, e-proposal, dashboard, dan peta publik Kabupaten Madiun.

## Status implementasi

- Phase 0: audit SRS/PRD/ERD dan rencana implementasi — selesai.
- Phase 1: autentikasi, RBAC, master wilayah, assignment Desa, activity log, dan admin shell — selesai.
- Phase 2: CRUD Kawasan, multi-kategori, desa anggota, dokumen privat, dan assignment Admin Kawasan — selesai.
- Phase 3: BUMDes/BUMDesma, legalitas, organisasi, unit/produk, aset, keuangan, kemitraan, dan galeri — selesai.
- Phase 4–6: rencana/realisasi, katalog potensi, dan E-Proposal — selesai.
- Phase 7: website BUMDes dan Company Profile admin — core tested; publikasi PDF publik masih blocked/TBD.
- Phase 8: peta/geospasial — core tested; UAT serta pembagian editor final masih TBD.
- Phase 9: dashboard Desa, Kawasan, dan DPMD — selesai.
- Phase 10: portal publik, optimasi query, baseline security headers, seeder demo, dan paket persiapan UAT — core selesai; UAT resmi serta kebijakan operasional produksi masih menunggu pemilik sistem.

## Stack lokal

- PHP 8.3+ (runtime terverifikasi: PHP 8.4)
- Laravel 13
- MySQL 5.7+
- Node.js + npm
- Blade, Tailwind CSS 4, dan Vite 8

## Menjalankan aplikasi

Kebutuhan lokal: PHP 8.3+, Composer, MySQL 5.7+, Node.js, dan npm. Ekstensi PHP yang diwajibkan Composer harus aktif. Aplikasi tidak memakai PostGIS; fitur peta menyimpan GeoJSON pada kolom JSON MySQL.

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Buat database MySQL bernama `si_madiun`, sesuaikan kredensial `DB_*` di `.env`, lalu jalankan:

```bash
php artisan migrate --seed
npm install --ignore-scripts
npm run build
php artisan serve
```

URL default: `http://127.0.0.1:8000`.

Dokumen dasar hukum disimpan pada disk privat Laravel. Batas upload bukan aturan bisnis permanen dan dapat diubah melalui:

```dotenv
KAWASAN_DOCUMENT_DISK=local
KAWASAN_DOCUMENT_MAX_SIZE_KB=10240
```

Tipe yang diterima adalah PDF, DOC, dan DOCX; unduhan selalu melewati route terautentikasi dan authorization kawasan.

Media BUMDes dan potensi juga disimpan pada disk privat dan dikirim melalui route terotorisasi atau projection publik. `php artisan storage:link` tidak diperlukan untuk file domain saat konfigurasi default dipakai.

Variabel penting lain:

```dotenv
APP_URL=http://localhost:8000
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=false
QUEUE_CONNECTION=database
BUMDES_WEBSITE_BASE_DOMAIN=bumdes.localhost
MAP_TILE_URL=https://tile.openstreetmap.org/{z}/{x}/{y}.png
```

Gunakan `SESSION_SECURE_COOKIE=true` saat production telah memakai HTTPS. Domain kustom BUMDes hanya dapat dirutekan setelah dimasukkan ke `BUMDES_VERIFIED_CUSTOM_DOMAINS` oleh pengelola hosting.

## Akun seed demo

Seeder development membuat tiga akun berikut. Kredensial ini hanya untuk lingkungan lokal/demo dan harus diganti atau tidak dijalankan di production.

| Role | Username | Kata sandi |
|---|---|---|
| Super Admin | `superadmin` | `DemoMadiun!2026` |
| Admin Kawasan | `adminkawasan` | `DemoMadiun!2026` |
| Admin Desa | `admindesa` | `DemoMadiun!2026` |

Seeder juga membuat record berawalan `DEMO -` untuk satu Kawasan, BUMDes, produk, peluang kemitraan, Potensi Published, dan E-Proposal Published. Seeder bersifat idempotent dan sengaja tidak membuat geometri palsu.

## Queue

Notifikasi E-Proposal memakai database notification. Jika `QUEUE_CONNECTION=database` dipakai untuk job tambahan di production, jalankan worker melalui process manager:

```bash
php artisan queue:work --tries=3
```

Saat ini tidak ada scheduler aplikasi yang wajib dijalankan.

## Pengujian

Suite memakai database MySQL terisolasi `si_madiun_test`. Buat database tersebut dan sesuaikan `phpunit.xml` bila kredensial lokal berbeda.

```bash
php artisan test --compact
vendor/bin/pint --format agent
npm run build
```

Pengujian keamanan dan acceptance mencakup RBAC, cross-scope IDOR, validasi upload, escaping output, public projection, rate limiting, dan baseline response headers. Checklist UAT manual tersedia di `docs/uat-checklist.md`.

Verifikasi final Phase 1–10 pada 21 September 2026: 334 test / 1251 assertion lulus, production build berhasil, dan audit dependency tidak menemukan vulnerability.

## Persiapan production

1. Jangan menjalankan demo seeder pada database production; migrasikan dengan `php artisan migrate --force`.
2. Gunakan `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, secret `APP_KEY` unik, kredensial database khusus aplikasi, dan `SESSION_SECURE_COOKIE=true`.
3. Atur permission tulis hanya untuk `storage` dan `bootstrap/cache`; jangan mengekspos disk privat sebagai static web root.
4. Jalankan `php artisan optimize` dan `npm ci --ignore-scripts && npm run build` pada artifact deployment.
5. Siapkan DNS wildcard/TLS untuk website BUMDes, tile provider produksi, backup database/file, retensi log, monitoring, dan prosedur restore sesuai kebijakan DPMD. Nilai SLA, retensi, kapasitas, serta RPO/RTO masih TBD di SRS/PRD.
6. Jalankan UAT dan isi bukti/sign-off sebelum go-live. Jangan menganggap automated test sebagai persetujuan bisnis.

## Dokumentasi

- `docs/implementation-plan.md` — phase, risiko, dan keputusan implementasi.
- `docs/requirement-traceability.md` — pemetaan setiap requirement SRS ke implementasi dan test.
- `docs/architecture.md` — struktur aplikasi serta batas tanggung jawab.
- `docs/database.md` — skema Phase 1–10, relasi, seeder demo, dan aturan integritas.
- `docs/roles-permissions.md` — matriks role, akses, dan scoping.
- `docs/uat-checklist.md` — skenario, bukti, dan sign-off UAT manual.
- `docs/eproposal-workflow.md` — transisi dan batas workflow E-Proposal.
