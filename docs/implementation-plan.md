# Audit Repository dan Implementation Plan

Tanggal audit: 19 September 2026
Status: **Phase 0–6 dan Phase 9 selesai; Phase 7 core tested dengan public PDF blocked/TBD; Phase 8 core tested dengan UAT dan pembagian editor final masih TBD**

## 1. Sumber Kebenaran

Audit menggunakan urutan sumber berikut:

1. `SRS - Sistem Informasi Kawasan Perdesaan Kabupaten Madiun.docx.pdf` (19 halaman, 26 Agustus 2026);
2. `prd.md` versi 0.1;
3. ERD konseptual pada Lampiran B SRS;
4. repository yang ada; dan
5. prompt implementasi sebagai arahan tambahan.

SRS dan PRD dibaca lengkap. Lampiran use-case serta ERD diperiksa secara visual karena keduanya berupa diagram. Tidak ditemukan ERD lain di repository.

## Hasil Implementasi Phase 1

Phase 1 diselesaikan setelah perintah eksplisit pengguna `lanjut phase 1`:

- runtime lokal dipindahkan dari SQLite yang tidak memiliki driver ke MySQL 5.7.33 yang tersedia dan diizinkan SRS;
- autentikasi email/username, logout, rate limit, reset password, tiga role, dashboard role-aware, dan profil selesai;
- master Kabupaten → Kecamatan → Desa, assignment many-to-many Admin Desa, serta secure cross-desa scoping selesai;
- activity log mencatat login/logout dan mutation penting pengguna, profil, serta wilayah;
- admin shell Blade/Tailwind responsif dan aksesibel selesai tanpa dependency runtime baru;
- seed demo tiga role dan database test MySQL terisolasi tersedia;
- 22 test/83 assertion lulus setelah cleanup, production build berhasil, dan enam viewport target terverifikasi tanpa page overflow.

AUT-F-004 diselesaikan pada Phase 2 melalui `user_kawasan`, scoped detail Kawasan, dan negative IDOR tests. Keputusan MySQL hanya mengikat fondasi saat ini; strategi geometry/PostGIS tetap diputuskan pada Phase 8 melalui abstraction yang tidak mengubah kontrak domain.

## Hasil Implementasi Phase 2

- CRUD Kawasan dengan status `DRAFT`/`DITETAPKAN`, soft delete, search, filter, dan pagination;
- desa anggota dan kategori multi-relasi dikelola dalam transaction dengan unique pair database;
- master kategori aktif/nonaktif serta empat seed awal sesuai contoh SRS;
- SK Bupati/TKPKP tersimpan pada Laravel Storage privat dengan limit environment-configurable;
- assignment many-to-many Admin Kawasan, dashboard assigned scope, detail read-only, dan cross-kawasan 404;
- mutation penting menghasilkan activity log tanpa membuat sistem audit baru;
- 30 test Phase 2/123 assertion dan total 52 test/206 assertion lulus; build dan audit dependency berhasil.

## Hasil Implementasi Phase 3

- profil BUMDes/BUMDesma, relasi multi-desa dengan Desa utama, legalitas, pencarian/filter/pagination, dan detail sembilan tab selesai;
- dokumen AD/ART/PERDES/PERKADES beserta ringkasan maksud/tujuan, modal awal, dan pembagian SHU tersimpan pada storage private;
- pengurus, unit usaha, produk/layanan, aset, keuangan tahunan, kemitraan/kebutuhan investasi, dan galeri gambar memiliki endpoint parent-scoped;
- Super Admin memiliki akses penuh, Admin Desa mengelola BUMDes pada Desa assignment-nya, dan Admin Kawasan membaca BUMDes dalam Kawasan assignment-nya;
- 52 skenario Phase 3/164 assertion lulus; full regression suite mencapai 104 test/370 assertion.

## Hasil Implementasi Phase 4

- matrix rencana aksi tahunan memiliki target numerik/deskriptif, periode bulanan/triwulanan/tahunan, pencarian, filter tahun, pagination, dan detail periodik;
- realisasi disimpan sebagai baris inkremental per periode dengan database unique constraint, parent-scoped IDOR protection, dan activity log;
- `ActionAchievementCalculator` menjadi source of truth presisi desimal untuk persentase dan status; nilai di atas 100% tidak dipotong, sedangkan target nol/null tidak menghasilkan persentase;
- `BuildAchievementSummary` menyiapkan agregasi reusable BUMDes, Kawasan, dan Kabupaten/PMD tanpa membuat Dashboard Phase 9;
- Super Admin dapat mengelola seluruh data, Admin Desa mengelola scope Desa sendiri, dan Admin Kawasan mendapat akses read-only pada Kawasan assignment;
- 40 skenario Phase 4/86 assertion dan full regression 144 test/456 assertion lulus. Aturan dan asumsi dicatat di `docs/action-plan-achievement.md`.

## 2. Ringkasan Eksekutif

Bagian audit di bawah merekam kondisi repository **sebelum** Phase 1: scaffold Laravel 13 belum berisi aplikasi domain Sistem Informasi dan Katalog Kawasan Perdesaan. Saat itu hanya ada halaman sambutan bawaan, satu route aplikasi, model `User`, tiga migration infrastruktur Laravel, satu seeder contoh, serta dua example test.

Fondasi dependency PHP terpasang dan `composer audit` bersih, tetapi runtime lokal belum siap untuk pengembangan aplikasi:

- konfigurasi memakai SQLite dan session database;
- file SQLite berukuran 0 byte;
- extension `pdo_sqlite` tidak tersedia;
- `GET /` menghasilkan HTTP 500 karena session tidak dapat membaca database;
- `node_modules` dan lockfile JavaScript belum ada; dan
- repository tidak memiliki metadata Git.

SRS mengizinkan MySQL atau PostgreSQL dan menyebut PostGIS untuk data peta. Karena repository belum memiliki database domain atau data existing yang harus dipertahankan, rekomendasi teknis adalah PostgreSQL + PostGIS. Keputusan tersebut harus dikonfirmasi dan environment-nya disiapkan sebelum Phase 1.

Kondisi runtime tersebut telah diselesaikan pada Phase 1 menggunakan MySQL lokal. PostgreSQL/PostGIS tidak tersedia di environment dan tetap menjadi opsi yang harus dinilai khusus untuk Phase 8.

## 3. Existing Architecture

### Backend

- Laravel `13.29.0` dengan PHP constraint `^8.3`; PHP CLI lokal `8.4.25`.
- Bootstrap menggunakan struktur Laravel modern di `bootstrap/app.php`.
- Arsitektur masih default skeleton, belum modular monolith.
- Hanya ada `App\Models\User`, `AppServiceProvider`, dan controller dasar kosong.
- Route aplikasi hanya `GET /` berupa closure yang merender `welcome.blade.php`.
- Belum ada authentication flow, RBAC, middleware domain, Form Request, Policy/Gate domain, Action/Service, Notification, activity log, atau route admin.

### Frontend

- Blade + Vite `8` + Tailwind CSS `4` dengan konfigurasi CSS-first.
- `resources/views/welcome.blade.php` masih halaman Laravel bawaan.
- `resources/js/app.js` kosong; `resources/css/app.css` hanya memuat Tailwind dan font Instrument Sans.
- Belum ada admin shell, public portal, component library, design token produk, Leaflet, atau behavior interaktif domain.
- UI tidak dapat dinilai pada viewport target karena request berhenti pada HTTP 500 sebelum view domain dirender.

### Struktur dan konvensi

- Direktori `app/` hanya berisi struktur default `Http`, `Models`, dan `Providers`.
- Sweep konvensi tidak menemukan tiga contoh konsisten untuk pola arsitektur non-default.
- Tidak ada `.ai/rules`, `pint.json`, PHPStan, atau Rector config.
- Karena codebase masih terlalu kecil, tidak ada durable convention yang layak direkam. Laravel defaults dan pedoman proyek menjadi baseline sementara.

## 4. Existing Dependencies

### PHP langsung

| Package | Version | Keterangan |
|---|---:|---|
| `laravel/framework` | 13.29.0 | Framework utama |
| `laravel/tinker` | 3.0.2 | REPL development |
| `fakerphp/faker` | 1.24.1 | Factory data |
| `laravel/boost` | 2.7.0 | Tooling/guidance proyek |
| `laravel/pail` | 1.2.7 | Log development |
| `laravel/pao` | 1.1.4 | Output test terstruktur |
| `laravel/pint` | 1.30.5 | Formatter |
| `mockery/mockery` | 1.6.15 | Test doubles |
| `nunomaduro/collision` | 8.9.5 | CLI error reporting |
| `phpunit/phpunit` | 12.5.34 | Test framework |

Belum ada dependency untuk auth scaffolding, RBAC pihak ketiga, PDF, Leaflet, media management, audit log, atau PostGIS. Dependency baru tidak boleh ditambahkan tanpa keputusan teknis dan approval.

### JavaScript langsung

| Package | Constraint | Keterangan |
|---|---:|---|
| `tailwindcss` | `^4.0.0` | Styling |
| `@tailwindcss/vite` | `^4.0.0` | Integrasi Tailwind-Vite |
| `vite` | `^8.0.0` | Bundler |
| `laravel-vite-plugin` | `^3.1` | Integrasi Laravel-Vite |
| `concurrently` | `^10.0.3` | Development processes |
| `@laravel/multiplex` | `^0.4.1` | Optional dependency |

Tidak ada `node_modules` maupun npm/pnpm/yarn lockfile, sehingga versi JS terpasang dan reproducibility build belum dapat diverifikasi.

## 5. Existing Database State

- Default connection: `sqlite`.
- `database/database.sqlite`: ada, 0 byte.
- Schema inspection: tidak ada tabel.
- Migration files hanya mendefinisikan tabel bawaan:
  - `users`, `password_reset_tokens`, `sessions`;
  - `cache`, `cache_locks`;
  - `jobs`, `job_batches`, `failed_jobs`.
- `php artisan migrate:status` gagal dengan `could not find driver`.
- Extension aktif hanya menyediakan `PDO` dan `pdo_mysql`; `pdo_sqlite` dan `pdo_pgsql` tidak tersedia.
- Belum ada migration domain, foreign key domain, data wilayah, spatial index, atau data existing yang perlu dimigrasikan.
- Konfigurasi masih `APP_NAME=Laravel`, timezone `UTC`, locale `en`, debug aktif, session/cache/queue berbasis database.

Implikasi: database final dan driver development/test harus disiapkan sebelum migration Phase 1. Jika PostgreSQL/PostGIS disetujui, local/CI/prod perlu extension `pdo_pgsql`, server PostgreSQL, extension PostGIS, dan strategi test yang tidak menyembunyikan perbedaan driver.

## 6. Historical Coverage Setelah Phase 3

Tabel berikut adalah arsip audit setelah Phase 3, bukan coverage terkini. Matrix terkini pada `docs/requirement-traceability.md` menjadi sumber status requirement.

| Area | Implementasi | Automated test | Status |
|---|---:|---:|---|
| AUT-F-001 s.d. AUT-F-006 | 6/6 | 6/6 | Phase 1–2 selesai |
| BUM-F-001 s.d. BUM-F-018 | 7/18 | 7/18 | BUM-F-001–007 selesai; 008–018 belum dimulai/TBD |
| KAT-F-001 s.d. KAT-F-004 | 4/4 | 4/4 | Phase 5 selesai |
| EPR-F-001 s.d. EPR-F-008 | 0/8 | 0/8 | Belum dimulai/TBD pada beberapa acceptance rule |
| KWS-F-001 s.d. KWS-F-004 | 4/4 | 4/4 | Phase 2 selesai dan teruji |
| DSH-F-001 s.d. DSH-F-003 | 0/3 | 0/3 | Belum dimulai |
| PET-F-001 s.d. PET-F-007 | 0/7 | 0/7 | Belum dimulai/TBD pada pembagian hak edit |
| **Total** | **17/50** | **17/50** | **34% implemented dan tested** |

Coverage hanya dihitung setelah behavior, authorization, UI internal yang relevan, dan acceptance test tersedia. Phase setelah Kawasan tetap tidak dihitung dari scaffold atau rancangan saja.

## 7. Gap terhadap SRS/PRD

| Domain | Gap utama |
|---|---|
| Authentication & Access | Fitur Phase 1–2 selesai; permission domain phase berikutnya belum ada. |
| Master Wilayah | Hierarki dan assignment Desa selesai; sumber data wilayah resmi/import tetap TBD. |
| Kawasan | CRUD, desa anggota, multi-kategori, dokumen hukum, dan Admin Kawasan selesai; geometri tetap Phase 8. |
| BUMDes/BUMDesma | BUM-F-001–007 dan galeri gambar pendukung selesai; rencana aksi, realisasi, publication state, website, domain, serta company profile tetap Phase 4/7. |
| Rencana Aksi | Tidak ada target, realisasi berkala, computation service, bukti, status, atau agregasi. |
| Potensi Desa | Tidak ada kategori, media, relasi BUMDes, search/filter/pagination, atau public scope. |
| E-Proposal | Tidak ada state transition guard, review/history immutable, verifier scope, notification, atau PDF. |
| Website/PDF | Tidak ada website BUMDes, routing subdomain/custom domain, company profile, proposal template, atau authorized download. |
| Map | Tidak ada Leaflet, map editor, spatial storage, marker/polygon, filter layer, atau endpoint payload ringan. |
| Dashboard/Public Portal | Tidak ada dashboard berjenjang, public navigation, metadata, responsive layout, atau accessibility implementation. |
| Security/Operations | Tidak ada IDOR test, private document delivery, upload policy, backup/retention decision, monitoring, atau production runbook. |

## 8. Discrepancy dan TBD

### Discrepancy antar-sumber

1. **ERD SRS vs multi-kategori.** Lampiran ERD menyimpan `kategori_tipologi` langsung pada Kawasan, sedangkan KWS-F-002 mewajibkan satu Kawasan memiliki lebih dari satu kategori. Proposed resolution: master `kategori_kawasan` + pivot `kawasan_kategori`; diagram diperlakukan konseptual.
2. **ERD SRS vs BUMDesma lintas desa.** ERD memakai satu `id_desa` pada BUMDes/BUMDesma, sedangkan BUM-F-001 menyebut desa/kelompok desa dan glosarium mendefinisikan BUMDesma lintas desa. Proposed resolution: `bumdes_desa` pivot.
3. **ERD SRS vs relasi kawasan-desa.** ERD memakai `desa.id_kawasan`; prompt meminta pivot `kawasan_desa`. SRS memastikan satu kawasan berisi beberapa desa, tetapi belum tegas apakah satu desa dapat menjadi anggota beberapa kawasan. Cardinality final harus dikonfirmasi.
4. **Functional table vs uraian kewenangan Super Admin.** KWS-F-001 hanya menyatakan Super Admin membuat Kawasan, tetapi bagian kebutuhan pengguna menyebut persetujuan/penolakan pengajuan pembentukan Kawasan dan BUMDes baru. Aktor pengaju, status, serta alurnya tidak didefinisikan. Jangan membangun approval workflow tanpa requirement tambahan.
5. **Phase dependency.** AUT-F-004 melintasi Phase 1–2. Assignment Desa dan Kawasan kini selesai dan diuji setelah entitas Kawasan tersedia.
6. **PHP runtime.** SRS menyebut PHP 8.3, sedangkan local runtime 8.4.25. Composer menerima keduanya. CI harus membuktikan kompatibilitas dengan minimum PHP yang disepakati.

### TBD yang membutuhkan keputusan

1. Akses “masyarakat” untuk E-Proposal: tanpa akun, akun khusus, atau melalui Admin Desa.
2. Exact transition matrix E-Proposal, terutama submit ulang setelah `PERLU_REVISI`, aturan edit, pembatalan, dan rejection bila ada.
3. Mekanisme/status publication Company Profile dan pihak yang berwenang mengubahnya.
4. Formula capaian untuk target nol/null, target waktu/non-numerik, pembulatan, nilai melebihi target, dan agregasi lintas periode.
5. Cardinality assignment user dan aturan overlap kewenangan BUMDesma.
6. Database final serta ketersediaan PostGIS pada local, CI, dan production.
7. Sumber data wilayah resmi, kode referensi, format import, dan ownership update.
8. Daftar master kategori kawasan/potensi final.
9. MIME, ukuran, jumlah file, resolusi gambar, storage video, dan retention; semua batas harus configurable.
10. Data keuangan, kemitraan, legalitas, dan media mana yang boleh dipublikasikan.
11. Template resmi PDF: kop, logo, penandatangan, nomor dokumen, font, watermark, dan snapshot/versioning.
12. Channel notifikasi di luar internal database notification.
13. Base domain final, verifikasi custom domain, TLS, dan proses DNS.
14. Hak edit map per role/objek, warna polygon multi-kategori, SRID, overlap, dan precision.
15. Definisi denominator/periode metric dashboard serta freshness/caching.
16. SLA, response time, concurrent users, storage, RPO/RTO, backup, disaster recovery, dan retensi audit.
17. Target browser/perangkat, standar aksesibilitas, KPI adopsi, dan acceptance threshold UAT.

## 9. Proposed Database Entities

Nama tabel berikut provisional sampai review ERD rinci. Relasi dinormalisasi untuk memenuhi requirement fungsional, bukan menyalin kolom gabungan pada ERD konseptual.

### Phase 1 — Foundation, Access, dan Wilayah (selesai)

- `users` dengan `username` opsional unik dan satu role backed enum sebagai baseline; jangan menambah permission package sebelum kebutuhan permission configurable dikonfirmasi.
- `kabupaten`, `kecamatan`, `desa`.
- `user_desa` untuk assignment eksplisit; assignment BUMDes dan Kawasan dilengkapi saat entitas induknya tersedia.
- `activity_logs` dengan actor, action, subject type/id, timestamp, IP nullable, dan metadata tersanitasi.
- tabel Laravel `password_reset_tokens`, `sessions`, `notifications`, cache/queue sesuai driver final.

Jika satu akun ternyata dapat memiliki lebih dari satu role, desain diubah sebelum migration menjadi `roles` + `role_user`. SRS/ERD saat ini hanya menunjukkan satu role per pengguna.

### Phase 2 — Kawasan Perdesaan

- `kawasan_perdesaan`, `kategori_kawasan`;
- `kawasan_desa`, `kawasan_kategori`;
- `dokumen_kawasan`, `user_kawasan`.

### Phase 3 — BUMDes/BUMDesma

- `bumdes`, `bumdes_desa`; ownership memakai assignment `user_desa`, bukan pivot user baru;
- `dokumen_bumdes`, `pengurus_bumdes`;
- `unit_usaha`, `produk_layanan`;
- `aset_bumdes`, `keuangan_bumdes`;
- `kemitraan_bumdes`, `galeri_bumdes`.

### Phase 4 — Rencana Aksi dan Realisasi

- `rencana_aksi`, `realisasi_aksi`;
- `bukti_realisasi` bila satu realisasi dapat memiliki lebih dari satu file.

### Phase 5–7 — Potensi, E-Proposal, dan Publikasi

- `kategori_potensi`;
- `potensi_desa` sebagai aggregate utama untuk data potensi dan workflow agar data proposal/publikasi tidak diduplikasi;
- `media_potensi`;
- `eproposal` sebagai workflow one-to-zero-or-one dari `potensi_desa`;
- `review_eproposal` sebagai audit transisi immutable;
- `website_bumdes`;
- `generated_documents` hanya jika snapshot, checksum, dan histori file hasil generate dibutuhkan.

### Phase 8 — Geospatial

- geometry polygon pada `kawasan_perdesaan` atau tabel geometry khusus setelah strategi PostGIS disepakati;
- `kategori_titik_peta`, `titik_peta`;
- spatial point untuk kantor BUMDes dan potensi, dengan abstraction GeoJSON/lat-long bila PostGIS tidak tersedia.

Dashboard tidak menjadi source-of-truth table. Metric dihitung dari domain entities melalui scoped aggregate query; cache/materialized view hanya ditambah setelah profiling.

## 10. Proposed Architecture

- Modular monolith dengan folder berbasis concern/domain tanpa package atau microservice terpisah.
- Controller menangani HTTP orchestration; operasi transaksi/workflow ditempatkan pada focused Action/Service.
- Form Request untuk validasi dan authorization yang substantial.
- Policy/Gate + query constraints untuk mencegah IDOR; menu hiding hanya presentasi.
- Database constraints dan transaction untuk invariant serta operasi multi-table.
- Backed enum untuk role, status proposal, periode, dan status aksi yang stabil.
- Laravel Storage private sebagai default dokumen; download melalui controller/policy.
- Laravel database notifications sebagai channel minimum EPR-F-005.
- Blade + Tailwind 4 untuk UI; Leaflet baru ditambahkan pada Phase 8.
- Native Laravel auth flow diprioritaskan agar tidak menambah dependency tanpa approval.
- Feature tests untuk behavior HTTP/security; unit tests hanya untuk computation/state transition murni.

## 11. Proposed Implementation Phases

| Phase | Scope | Exit criteria minimum |
|---:|---|---|
| 0 | Audit, source review, discrepancy, plan, traceability | SRS/PRD/ERD dibaca; plan dan matrix akurat; Phase 1 belum dimulai. |
| 1 | Runtime/database foundation, auth, role, wilayah, assignment Desa, activity log, admin shell | **Selesai:** app dirender; migration/factory/seeder berjalan; auth/reset/profile/RBAC/IDOR/activity tests lulus. |
| 2 | Kawasan, kategori, desa anggota, dokumen, Admin Kawasan | **Selesai:** CRUD scoped, multi-kategori, assignment Kawasan, private file tests lulus. |
| 3 | BUMDes/BUMDesma dan child data | **Selesai:** multi-desa, ownership, CRUD BUM-F-001–007, galeri gambar, media/private docs, dan tests lulus. |
| 4 | Rencana aksi, realisasi, capaian | Formula disetujui, status/periode, bukti, aggregate tests lulus. |
| 5 | Potensi desa dan katalog | CRUD/media/publication, search/filter/pagination, public-scope tests lulus. |
| 6 | E-Proposal, history, notification, proposal PDF | **Selesai:** transition matrix, immutable history, verifier scope, protected PDF, notification, catalog integration, dan tests lulus. Jalur masyarakat tetap TBD. |
| 7 | Website BUMDes dan company profile PDF | Configurable domain, publication rules, responsive public site, printable PDF, tests lulus. |
| 8 | Map/geospatial | Admin editor, public layers, spatial/fallback storage, lightweight endpoint, tests lulus. |
| 9 | Dashboard tiga level | **Selesai:** agregat Desa/Kawasan/DPMD benar, scoped per role, query count terbatasi, filter tahun, peta Kawasan, tests dan browser QA lulus. |
| 10 | Public portal, optimization, UAT, security, docs | Responsive/a11y checks, security review, production build, UAT evidence, runbook selesai. |

Setiap phase mengikuti urutan: migration → model/relationship → factory/seeder → Action/Service → Policy → Form Request → controller/routes → UI → tests → documentation → traceability.

## 12. File yang Dibuat/Diubah pada Phase 1

### Existing files yang diperkirakan diubah

- `.env.example`;
- `config/app.php`, `config/auth.php`, `config/session.php`, dan config domain bila diperlukan;
- `bootstrap/app.php`, `bootstrap/providers.php`;
- `app/Models/User.php`;
- `routes/web.php`;
- `database/seeders/DatabaseSeeder.php`;
- `resources/css/app.css`, `resources/js/app.js`;
- `README.md`;
- `docs/implementation-plan.md`, `docs/requirement-traceability.md`.

### New files/directories yang diperkirakan dibuat

- migration untuk auth/role, wilayah, `user_desa`, dan `activity_logs`;
- `app/UserRole.php`;
- models/factories/seeders Kabupaten, Kecamatan, Desa, dan ActivityLog;
- `app/Http/Controllers/Auth/**`, controller profil, pengguna, dan wilayah;
- `app/Http/Requests/**` untuk auth, profil, pengguna, assignment, dan wilayah;
- `app/Policies/**` serta middleware/query scoping yang diperlukan;
- focused Action `RecordActivity` untuk activity logging;
- listener/subscriber auth untuk login activity;
- Blade layouts/components untuk auth dan admin shell Bahasa Indonesia;
- Feature tests auth, reset, profil, RBAC, assignment Desa, IDOR, wilayah, dan activity log;
- Unit tests untuk pure domain rules bila ada;
- `docs/architecture.md`, `docs/database.md`, `docs/roles-permissions.md`.

Migration bertimestamp dibuat melalui Artisan. `user_kawasan` tidak dibuat sebelum `kawasan_perdesaan` tersedia pada Phase 2.

## 13. Risiko Teknis

| Risiko | Level | Mitigasi |
|---|---|---|
| Database geometry final belum diputuskan | Tinggi | MySQL fondasi sudah aktif; putuskan PostgreSQL/PostGIS atau fallback geometry sebelum Phase 8. |
| Aplikasi HTTP 500 karena SQLite | Ditutup | MySQL, migration, session, dan browser smoke check berhasil. |
| ERD bertentangan dengan requirement multi-relasi | Tinggi | Review cardinality; gunakan pivots setelah discrepancy disetujui. |
| Scope wilayah/BUMDesma menimbulkan IDOR | Tinggi | Central policy/query constraints dan negative cross-scope tests. |
| Workflow proposal belum lengkap | Tinggi | Explicit state matrix; jangan menambah state/transisi tanpa keputusan. |
| Dokumen private dapat terekspos | Tinggi | Private disk, authorized download, MIME/size config, security tests. |
| PostGIS tidak tersedia | Tinggi | Spatial abstraction + GeoJSON/lat-long fallback. |
| Formula capaian belum final | Tinggi | Isolasi computation service; tandai blocked sampai edge-case disetujui. |
| Dashboard/map payload mahal | Sedang-Tinggi | Aggregate query, select minimum, pagination, index/query plan, profiling. |
| PDF/media menghabiskan resource | Sedang | Limit configurable, optimization, queue bila perlu, storage lifecycle. |
| Tidak ada Git metadata | Tinggi | Restore/init version control sebelum Phase 1. |
| Tidak ada JS lockfile/install | Ditutup | npm dipilih, `package-lock.json` dibuat, audit dan production build lulus. |
| PHP local 8.4 vs SRS 8.3 | Sedang | CI pada minimum yang disepakati; hindari API eksklusif 8.4. |
| Timeline 30 man-days untuk 50 requirement | Tinggi | Gate per phase, acceptance/UAT eksplisit, jangan menyatakan selesai tanpa test. |

## 14. Baseline Verification Phase 0

- `composer show --direct`: dependency PHP terinventarisasi.
- `composer validate --no-check-publish`: valid.
- `composer audit`: tidak ada advisory keamanan.
- `php artisan route:list --except-vendor`: 1 application route.
- `php artisan test --compact`: 2 tests lulus, 2 assertions; keduanya example test.
- `php artisan migrate:status`: gagal, `pdo_sqlite` tidak tersedia.
- Browser desktop `1440 × 900`: `GET /` menampilkan Laravel HTTP 500 `could not find driver` saat membaca session.
- Browser console: tidak ada log frontend; Laravel browser log belum tersedia.
- Mobile/tablet viewport checks tidak bermakna karena request berhenti pada error backend.
- Frontend build tidak dijalankan karena dependency JavaScript belum terpasang dan tidak ada lockfile.

## 15. Verification Phase 1

- MySQL schema dan seed berhasil; tiga role serta tiga desa demo tersedia.
- `php artisan test --compact`: 22 test lulus, 83 assertion setelah cleanup.
- `vendor/bin/pint --dirty --format agent`: source PHP diformat pada Git worktree.
- `npm run build`: production bundle berhasil; npm audit 0 vulnerability.
- Browser desktop: login, dashboard, pengguna, dan master wilayah dirender tanpa console error.
- Browser responsive: 1440×900, 1280×800, 1024×768, 768×1024, 390×844, dan 360×800; tidak ada page-level horizontal overflow.
- Mobile drawer: buka/tutup, focus, overlay, dan Escape-to-close berfungsi; tabel lebar scroll di container lokal.
- Log backend: tidak ada error baru setelah build dan browser verification. Entri error terakhir berasal dari iterasi test sebelum manifest tersedia.

## 16. Verification Phase 2

- Enam migration Phase 2 berhasil pada MySQL 5.7; schema aktual diverifikasi berikut foreign key, index, dan unique constraint.
- `vendor/bin/pint --dirty --format agent`: seluruh PHP yang berubah sesuai formatter.
- `php artisan test --compact`: 52 test lulus, 206 assertion; 30 test/123 assertion khusus Phase 2.
- `npm run build`: bundle production Blade/Tailwind/Vite berhasil.
- `composer validate`, `composer audit`, dan `npm audit --omit=dev`: berhasil tanpa advisory.
- `php artisan route:list --except-vendor`: 52 route terdaftar.
- Browser visual/responsive Phase 2 belum dapat diverifikasi ulang karena runtime kontrol browser lokal gagal diinisialisasi. Ini dicatat sebagai keterbatasan verifikasi, bukan diklaim lulus.

## 17. Verification Phase 3

- sepuluh migration Phase 3 mendefinisikan profil, pivot Desa, dokumen, pengurus, unit/produk, aset, keuangan, kemitraan, dan galeri;
- 52 skenario khusus Phase 3/164 assertion dan full regression 104 test/370 assertion lulus, termasuk ownership, validation, XSS escaping, unique constraint, private storage, dan parent-child IDOR;
- `migrate:fresh --seed` berhasil pada `si_madiun_test`; semua migration Phase 3 berstatus `Ran` pada database development tanpa destructive reset;
- Pint lulus; Composer valid tanpa advisory, npm audit 0 vulnerability, dan production build Vite/Tailwind berhasil;
- 85 route aplikasi terdaftar, termasuk 33 route BUMDes yang seluruh child-nya parent-scoped;
- browser memverifikasi create → detail, profil, dan tab kemitraan pada 1440×900, 1280×800, 1024×768, 768×1024, 390×844, dan 360×800 tanpa page-level overflow;
- drawer mobile, scroll lokal tab, Escape-to-close, console, dan log server diperiksa tanpa error baru.

## 18. Verification Phase 4

- dua migration aditif Phase 4 berhasil pada MySQL 5.7 dan memakai FK `RESTRICT`, index, unique rencana+periode, serta kolom `DECIMAL`;
- 40 skenario khusus Phase 4/86 assertion lulus untuk BUM-F-008–011; full regression mencapai 144 test/456 assertion;
- formula dan status hanya berada pada action terpusat; list melakukan eager loading dan summary melakukan aggregate query tanpa loop-query per BUMDes;
- UI matrix, form, detail, dua belas/empat/satu slot periode, reduced-motion-safe navigation, dan state read-only Admin Kawasan tersedia;
- `migrate:fresh --seed` pada `si_madiun_test`, 94 route, Blade view cache, production build, Composer/npm audit, serta smoke flow HTTP login → BUMDes → rencana → realisasi → matrix berhasil;
- kontrol browser desktop tidak dapat diinisialisasi pada host saat verifikasi, sehingga pemeriksaan visual enam viewport tidak diklaim; layout tetap dilindungi oleh pola responsive existing dan render HTTP/build;
- implementasi Phase 5 selesai; E-Proposal/Phase 6 dicatat pada verifikasi berikutnya. UI Dashboard Phase 9 dan fitur Phase 7 tidak dibangun.

## 19. Verification Phase 5

1. tiga migration aditif (`kategori_potensi`, `potensi_desa`, `media_potensi`) berhasil pada MySQL 5.7.33 dengan foreign key, index, `DECIMAL(19,2)`, dan soft delete potensi;
2. 38 test Phase 5 dengan 104 assertion lulus, mencakup CRUD/ownership, kategori, relasi BUMDes same-village/BUMDesma, media/Storage fake, IDOR, publication scope, search/filter/pagination, escaping, dan activity log;
3. full regression suite Phase 1–5 mencapai 182 test dan 560 assertion lulus;
4. status katalog Phase 5 hanya `DRAFT`/`PUBLISHED`; publication manual hanya Super Admin dan tidak mengklaim workflow E-Proposal;
5. browser smoke check katalog publik berhasil pada 1440×900, 1280×800, 1024×768, 768×1024, 390×844, dan 360×800; pencarian, responsive layout tanpa page-level horizontal overflow, dan console tanpa error terverifikasi.

## 20. Gate Sebelum Phase 6

1. definisikan transition matrix E-Proposal (`DIAJUKAN`, `PERLU_REVISI`, `TERVERIFIKASI`, `PUBLISHED`) dan aktor/verifier sebelum menambah state baru;
2. tetapkan histori review immutable, notifikasi, dan aturan edit setelah submission;
3. jangan mengubah katalog publik Phase 5 menjadi submission workflow secara diam-diam.

## 21. Verification Phase 6

- tiga migration Phase 6 membuat `eproposal`, `review_eproposal`, dan `notifications` pada MySQL 5.7.33; proposal one-to-zero-or-one memakai unique FK dan review memakai FK historical non-cascade;
- `EProposalStatus` memisahkan empat status SRS dari `PotensiStatus`; `EProposalWorkflowService` memakai transition guard, `DB::transaction()`, dan `lockForUpdate()`;
- Admin Desa mengajukan serta resubmit proposal dalam scope Desa; Admin Kawasan mereview hanya desa anggota kawasan; publication atomic menyinkronkan E-Proposal dan Potensi Desa;
- Laravel database notification `EProposalStatusChanged` menjadi satu-satunya trigger pada service workflow; tidak ada channel eksternal yang diasumsikan;
- `ProposalPdfService` dan template DomPDF `^3.1.2` hanya mengizinkan Verified/Published. Admin Kawasan/Super Admin memakai policy scope; publik hanya Published + Potensi Published;
- 34 test Phase 6 dengan 159 assertion lulus; full regression Phase 1–6 mencapai 216 test dan 719 assertion, tanpa failing test;
- audit final memperbaiki eager loading kategori, preview data/galeri nyata, filter Desa pada review queue, notification inbox/read ownership, dan safe PDF image path/content. Status manual hanya untuk potensi tanpa proposal; potensi berproposal tidak dapat diarsipkan;
- browser smoke pada database `si_madiun_test` memverifikasi Admin Desa submit → revisi oleh Super Admin reviewer → edit data → resubmit Admin Desa → verify → publish → link PDF katalog. Scope/hak Admin Kawasan dibuktikan automated tests. Detail/queue dicek pada 1440×900, 1280×800, 1024×768, 768×1024, 390×844, dan 360×800 tanpa page-level horizontal overflow; console tidak menunjukkan warning/error dan tidak ada error server baru pada audit 17 September;
- PDF A4 dirender dan diperiksa secara visual; gallery image/content serta fallback missing/corrupt/path traversal dilindungi test. Build, Pint, Blade cache, migration status, Composer/npm production audit, dan diff whitespace checks berhasil;
- jalur masyarakat, proposal untuk potensi legacy Published, format kop/nomor resmi, channel notifikasi eksternal, unpublish, serta aturan freeze/snapshot data setelah verifikasi tetap TBD karena belum didefinisikan SRS/PRD;
- Phase 7 (website BUMDes/company profile) tidak dimulai.

## 22. Gate Sebelum Phase 7

Gate di bawah merekam rekomendasi audit Phase 6. Pengguna mengizinkan mulai Phase 7 pada 17 September 2026; unresolved E-Proposal/public submission tetap terisolasi dan tidak dijadikan aturan baru pada Phase 7.

1. tetapkan identitas, ownership, dan tracking untuk pengajuan masyarakat sebelum membuka public submission;
2. tetapkan format resmi kop, nomor proposal, tanda tangan, QR, dan aturan unpublish jika dibutuhkan;
3. lakukan UAT PDF dan workflow pada data resmi sebelum menambah website BUMDes atau company profile.

## 23. Implementasi Phase 7

- BUM-F-012–017 diimplementasikan pada arsitektur Laravel/Blade existing, tanpa dependency baru; `website_bumdes` menyimpan konfigurasi, bukan salinan profil/produk/media.
- Alamat bawaan slug+ID unik dibuat inactive untuk existing/new profiles. Base domain configurable; path `/website-bumdes/{subdomain}` menyediakan akses lokal yang tidak bergantung DNS.
- Settings domain/aktivasi memakai owner scope dan policy Admin Desa/Super Admin, transaction, parent lock, dan existing activity log. Custom domain hanya routable setelah verifikasi pengelola hosting melalui server allowlist; DNS/TLS tetap external dependency sesuai SRS.
- Public host tidak membuka auth/admin routes. Public projection mengecualikan contacts/foto pengurus privat, aset, keuangan, serta produk/unit nonaktif. Produk 12/page dan galeri 8/page; gallery PDF maksimum 12 foto pertama untuk membatasi embedding.
- `CompanyProfilePdfService` memuat profil, desa, organisasi, unit/produk, aset, keuangan, serta galeri; template formal A4 terpisah. Admin Desa own scope/Super Admin dapat download on-demand, no-store, audited; Admin Kawasan ditolak sesuai SRS.
- BUM-F-018 tetap BLOCKED/TBD: SRS menyebut published tetapi PRD 20.3/20.14 belum menetapkan penerbit/state dan financial disclosure. User meminta mengikuti SRS; website aktif tidak disamakan dengan published PDF, tidak dibuat workflow approval baru. Endpoint publik fail-closed 404, tanpa tombol publik.
- 42 test Phase 7 / 173 assertion dan full regression 258 test / 892 assertion lulus; full suite dijalankan ulang pada 18 September 2026 setelah perbaikan layout PDF dan tetap lulus.
- Browser QA pada 18 September menggunakan database `si_madiun_test`: Super Admin membuat profil beserta organisasi, unit/produk, aset, dan ringkasan keuangan dummy; Admin Desa mengaktifkan website, mengunduh PDF, dan menonaktifkan website. Website nonaktif menghasilkan 404. Subdomain bawaan lokal dan link kembali ke katalog aplikasi utama berhasil dibuka.
- Settings dan website publik diperiksa pada 1440×900, 1280×800, 1024×768, 768×1024, 390×844, dan 360×800 tanpa page-level horizontal overflow. Skip-link/focus keyboard dan navigasi anchor bekerja; tidak ada warning/error console pada alur normal. Tidak ada error server baru setelah MySQL tersedia; kegagalan seed awal saat MySQL belum berjalan dicatat sebelum QA.
- PDF A4 dua halaman dirender dan diperiksa visual. Bagian keuangan dijaga tetap bersama judul/header dan footer dipindahkan sebelum flow content agar muncul pada halaman lanjutan. Fixture visual memakai fallback foto kosong; embedding gambar valid dan penolakan missing/corrupt/path traversal dibuktikan automated tests. Build Vite, Blade cache, Pint, migration, dan diff whitespace checks berhasil. Server/tab QA serta file PDF/PNG sementara dibersihkan; data utama tidak ditambah.
- Format logo/kop/nomor/tanda tangan resmi, production base domain/DNS/TLS, dan UAT resmi tetap TBD. Status Phase 8 dicatat di bawah.

## 24. Implementasi Phase 8 — Geospatial fallback

- Pengguna menyetujui Phase 8 terpisah dari blocker public Company Profile. SRS halaman 13–14 menetapkan empat layer, Leaflet/OpenStreetMap, draw/drop-pin, dan public read-only. MySQL existing dipertahankan dengan JSON GeoJSON fallback; tidak ada migrasi engine, PostGIS, spatial index, atau intersection query.
- Migration aditif menambah nullable `geometri` pada Kawasan/BUMDes/Potensi serta `titik_informasi` dengan FK Kawasan RESTRICT dan unique kawasan+jenis+nama. Up berhasil pada database utama tanpa reset/data dummy. Down→up berhasil pada database test kosong; down destructive terhadap geometri/titik Phase 8 yang kelak diisi, sehingga backup diperlukan sebelum rollback produksi.
- `GeoJsonGeometry` memvalidasi Point dua dimensi dan Polygon satu ring luar: angka finite/range global, maksimum 64 KiB/500 simpul, closure, vertex unik, area nonzero, dan non-self-intersection. Normalisasi enam desimal; Feature/MultiPolygon/lubang/CRS tambahan belum didukung.
- Policy `manageMap` terpisah dari update profil/publication: Super Admin semua; Admin Kawasan batas dan titik dalam assignment termasuk informasi kawasan (SRS halaman 7); Admin Desa titik BUMDes/Potensi desa sendiri. Batas kawasan lintas desa dan informasi kawasan tidak dibuka kepada Admin Desa tanpa pembagian editor final. Scoped lookup sebelum authorization, parent lock, mutation dan audit atomic.
- `MapFeatureService` memakai explicit projection, eager loading, dan limit 100 feature/layer dengan peringatan truncation. Public hanya kawasan ditetapkan, informasi aktif pada parent ditetapkan/nonarsip, BUMDes nonarsip, dan Potensi Published/nonarsip. Tidak ada kontak/aset/keuangan/dokumen privat. Website aktif hanya mengaktifkan link website; public Company Profile tetap 404. Popup memuat maksimal tiga nama unit usaha aktif.
- Warna kategori stabil; kategori alfabetis pertama digunakan untuk warna tampilan, bukan prioritas bisnis. Semua kategori tersedia di popup/list/legend; dashed boundary menandai multi-kategori. Geometri resmi belum diberikan, sehingga seeder utama tidak membuat batas atau lokasi palsu.
- Leaflet 1.9.4 dibundel lewat dynamic import halaman peta, bukan CDN/global loading. URL tile dan attribution configurable; attribution OSM tetap terlihat, tanpa bulk/offline prefetch atau izin geolocation. `/peta` publik dan `/peta/kelola` internal tersedia, dengan empat toggle layer, HTML list fallback, search, dan pagination internal.
- Editor native Leaflet menyediakan drop/drag pin, tambah simpul, finish/undo/gambar ulang, koreksi simpul, clear, dan form save. Alternatif keyboard memakai pan/zoom dan tombol posisi tengah peta. Reduced-motion, map isolation, resize cleanup, Escape popup, safe text, dan status/loading/error telah diuji melalui browser.
- Build Vite, syntax JavaScript, Blade cache, Pint, dan live HTTP public map/data berhasil. 61 test Phase 8 / 266 assertion lulus untuk geometry, policy, HTTP scope/write, projection/publication, limits, dan audit rollback. Final full regression 319 test / 1158 assertion lulus. Satu fixture tes lama EProposalInterfaceTest dibuat deterministik dengan izin pengguna setelah collision substring Faker; query aplikasi dan security assertions dipertahankan.
- Inventaris browser sempat gagal tiga kali, tetapi koneksi tab langsung berhasil dipulihkan. QA pada `si_madiun_test` membuktikan draw/finish/koreksi poligon; drop/drag pin; koreksi simpul dan save dengan keyboard; create/deactivate titik informasi; empat toggle layer, empty state, popup, link katalog, dan mode public read-only. Popup fokus diperbaiki agar terbuka dari tombol daftar dan tetap terlihat di mobile. Super Admin, Admin Kawasan assigned-scope, Admin Desa own BUMDes/Potensi, serta cross-scope 404 dibuktikan. Peta publik dan editor diperiksa pada 1440×900, 1280×800, 1024×768, 768×1024, 390×844, dan 360×800 tanpa page-level horizontal overflow atau warning/error console. Fixture QA telah dibersihkan; database utama tidak menerima geometri dummy. UAT resmi, data koordinat produksi, provider tile produksi, pembagian editor Admin Desa final, dan keputusan PostGIS tetap TBD.
- Data/batas resmi dan UAT, prioritas warna multi-kategori resmi, kewenangan final editor (PRD 20.17), provider produksi/kapasitas (20.7), dan pilihan database/spatial final (20.6) tetap TBD. Implementasi Dashboard Phase 9 dicatat terpisah di bawah.

## 25. Implementasi Phase 9 — Dashboard dan Pelaporan

- `DashboardSummaryService` menjadi satu pintu proyeksi tiga dashboard tanpa menambah tabel metric. Controller hanya memvalidasi tahun, memanggil service, dan menambahkan peta scoped khusus Admin Kawasan.
- Admin Desa menerima desa tugas, BUMDes/BUMDesma dalam scope, produk aktif, rencana/realisasi per tahun, dan status E-Proposal. Indikator profil menghitung tujuh field inti sebagai kelengkapan operasional `n/7`, tanpa bobot atau klaim skor resmi.
- Admin Kawasan menerima rekap kawasan/desa/BUMDes, status proposal per kawasan, rata-rata capaian, dan `MapFeatureService` yang tunduk pada assignment. Super Admin menerima metric lintas kabupaten, published totals, rata-rata capaian, dan tabel rekap per kawasan.
- `BuildAchievementSummary` diperluas dengan batch summary per BUMDes dan Kawasan serta scope semua data yang terlihat pengguna. Pivot dibaca sekali dan hasil dikelompokkan in-memory; enam kawasan tetap selesai dalam paling banyak 12 query service.
- Lima `DashboardTest` / 28 assertion lulus untuk metric, tahun, cross-scope/IDOR, peta, proposal, capaian, dan query bound. Final full regression Phase 1–9 lulus 324 test / 1186 assertion; Pint, Blade cache, dan production build lulus.
- Browser memverifikasi Super Admin, Admin Kawasan, Admin Desa, serta filter tahun. Desktop 1440×900, tablet 768×1024, mobile 390×844 diperiksa visual; seluruh enam viewport target bebas page-level horizontal overflow dan console warning/error.

## 26. Implementasi Phase 10 — Portal Publik, Hardening, dan UAT

- Host utama sekarang membuka portal publik dengan navigasi Beranda, Kawasan, Potensi Desa, BUMDes, Peta, dan Tentang. Host tenant BUMDes tetap memakai resolver existing; pengguna internal dapat kembali ke workspace tanpa mengubah public projection.
- `PublicPortalService` menyusun hero/statistik, pencarian, Potensi Published, peta, Kawasan Ditetapkan, website BUMDes aktif, peluang kemitraan Dibutuhkan, dan E-Proposal Published dari data nyata. List/detail memakai eager loading, pagination, batas item, dan scope publik eksplisit.
- Detail Kawasan draft serta BUMDes tidak aktif menghasilkan 404. Blade escaping, CSRF existing, password hashing/auth server-side, parent-scoped lookup, login/reset/PDF throttling, session encryption flags, dan global security headers menjadi baseline keamanan Phase 10.
- `DemoDomainSeeder` membuat rangkaian data demo non-geospasial secara idempotent tanpa mengarang batas/titik. `docs/uat-checklist.md` memisahkan PASS/FAIL/BLOCKED dan mempertahankan gate bisnis/operasional yang belum diputuskan.
- Focused regression portal/security/seeder lulus 11 test / 60 assertion; broader focused regression lulus 84 test / 400 assertion. Final full suite Phase 1–10 lulus 334 test / 1251 assertion.
- Pint, Blade cache, production build Vite, Composer validation/audit, npm audit, migration status, dan diff whitespace check lulus. Composer hanya memberi warning existing tentang exact constraint DomPDF; tidak ada security advisory.
- Public Company Profile, identitas/tracking pengajuan masyarakat, pembagian final editor Admin Desa, data koordinat produksi, provider tile, PostGIS, SLA/RPO/retention, DNS/TLS, dan sign-off UAT resmi tetap TBD serta tidak diisi dengan asumsi implementasi.
