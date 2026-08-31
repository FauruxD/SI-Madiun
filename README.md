# SI Kawasan Perdesaan Kabupaten Madiun

Aplikasi Laravel untuk pengelolaan kawasan perdesaan, BUMDes/BUMDesma, potensi desa, e-proposal, dashboard, dan peta publik Kabupaten Madiun.

## Status implementasi

- Phase 0: audit SRS/PRD/ERD dan rencana implementasi — selesai.
- Phase 1: autentikasi, RBAC, master wilayah, assignment Desa, activity log, dan admin shell — selesai.
- Phase 2 dan seterusnya: lihat `docs/implementation-plan.md`.

## Stack lokal

- PHP 8.3+ (runtime terverifikasi: PHP 8.4)
- Laravel 13
- MySQL 5.7+
- Node.js + npm
- Blade, Tailwind CSS 4, dan Vite 8

## Menjalankan aplikasi

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

## Akun seed demo

Seeder development membuat tiga akun berikut. Kredensial ini hanya untuk lingkungan lokal/demo dan harus diganti atau tidak dijalankan di production.

| Role | Username | Kata sandi |
|---|---|---|
| Super Admin | `superadmin` | `DemoMadiun!2026` |
| Admin Kawasan | `adminkawasan` | `DemoMadiun!2026` |
| Admin Desa | `admindesa` | `DemoMadiun!2026` |

## Pengujian

Suite memakai database MySQL terisolasi `si_madiun_test`. Buat database tersebut dan sesuaikan `phpunit.xml` bila kredensial lokal berbeda.

```bash
php artisan test --compact
vendor/bin/pint --format agent
npm run build
```

## Dokumentasi

- `docs/implementation-plan.md` — phase, risiko, dan keputusan implementasi.
- `docs/requirement-traceability.md` — pemetaan setiap requirement SRS ke implementasi dan test.
- `docs/architecture.md` — struktur aplikasi serta batas tanggung jawab.
- `docs/database.md` — skema Phase 1 dan aturan integritas.
- `docs/roles-permissions.md` — matriks role, akses, dan scoping.
