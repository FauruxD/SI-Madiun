# Audit Repository dan Implementation Plan

Tanggal audit: 31 Agustus 2026  
Status: **Phase 0 dan Phase 1 selesai; Phase 2 belum dimulai**

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
- 22 test/82 assertion lulus, production build berhasil, dan enam viewport target terverifikasi tanpa page overflow.

AUT-F-004 tetap `IN PROGRESS` karena assignment Desa telah selesai dan diuji, sedangkan assignment Admin Kawasan baru dapat dibuat setelah entitas Kawasan tersedia pada Phase 2. Keputusan MySQL hanya mengikat fondasi Phase 1; strategi geometry/PostGIS tetap diputuskan pada Phase 8 melalui abstraction yang tidak mengubah kontrak domain saat ini.

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

## 6. Requirement Coverage Saat Ini

| Area | Implementasi | Automated test | Status |
|---|---:|---:|---|
| AUT-F-001 s.d. AUT-F-006 | 5 selesai + 1 parsial | 5 tested + assignment Desa tested | Phase 1 selesai; AUT-F-004 menunggu assignment Kawasan Phase 2 |
| BUM-F-001 s.d. BUM-F-018 | 0/18 | 0/18 | Belum dimulai |
| KAT-F-001 s.d. KAT-F-004 | 0/4 | 0/4 | Belum dimulai |
| EPR-F-001 s.d. EPR-F-008 | 0/8 | 0/8 | Belum dimulai/TBD pada beberapa acceptance rule |
| KWS-F-001 s.d. KWS-F-004 | 0/4 | 0/4 | Belum dimulai |
| DSH-F-001 s.d. DSH-F-003 | 0/3 | 0/3 | Belum dimulai |
| PET-F-001 s.d. PET-F-007 | 0/7 | 0/7 | Belum dimulai/TBD pada pembagian hak edit |
| **Total** | **0/50** | **0/50** | **0% implemented, 0% tested** |

Model/tabel `User` dan password reset token bawaan tidak dihitung sebagai implementasi AUT karena route, behavior, authorization, UI, dan acceptance test belum ada. Dua example test lulus, tetapi tidak membuktikan requirement domain.

## 7. Gap terhadap SRS/PRD

| Domain | Gap utama |
|---|---|
| Authentication & Access | Tidak ada login/reset/profile, role, assignment wilayah, policy, rate limiter, atau activity log. |
| Master Wilayah | Tidak ada Kabupaten, Kecamatan, Desa, import, maupun seed DEMO. |
| Kawasan | Tidak ada CRUD, desa anggota, multi-kategori, dokumen hukum, admin kawasan, atau geometri. |
| BUMDes/BUMDesma | Tidak ada profil multi-desa, legalitas, organisasi, unit usaha, produk, aset, keuangan, kemitraan, galeri, atau publication state. |
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
5. **Phase dependency.** AUT-F-004 meminta assignment Admin Kawasan pada Phase 1, sedangkan entitas Kawasan baru dijadwalkan Phase 2. Proposed resolution: Phase 1 menyelesaikan role serta assignment Desa; assignment Kawasan diselesaikan pada Phase 2 dan AUT-F-004 tetap `IN PROGRESS` sampai keduanya teruji.
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

- `bumdes`, `bumdes_desa`, `user_bumdes`;
- `dokumen_legalitas_bumdes`, `pengurus_bumdes`;
- `unit_usaha`, `produk_layanan`;
- `aset_bumdes`, `kinerja_keuangan_bumdes`;
- `kemitraan_bumdes`, `media_bumdes`.

### Phase 4 — Rencana Aksi dan Realisasi

- `rencana_aksi`, `realisasi_aksi`;
- `bukti_realisasi` bila satu realisasi dapat memiliki lebih dari satu file.

### Phase 5–7 — Potensi, E-Proposal, dan Publikasi

- `kategori_potensi`;
- `potensi_desa` sebagai aggregate utama untuk data potensi dan workflow agar data proposal/publikasi tidak diduplikasi;
- `media_potensi`;
- `proposal_histories`/`review_eproposal` sebagai audit transisi immutable;
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
| 2 | Kawasan, kategori, desa anggota, dokumen, Admin Kawasan | CRUD scoped, multi-kategori, assignment Kawasan, private file tests lulus. |
| 3 | BUMDes/BUMDesma dan child data | Multi-desa, ownership, CRUD lengkap, media/private docs, tests lulus. |
| 4 | Rencana aksi, realisasi, capaian | Formula disetujui, status/periode, bukti, aggregate tests lulus. |
| 5 | Potensi desa dan katalog | CRUD/media/publication, search/filter/pagination, public-scope tests lulus. |
| 6 | E-Proposal, history, notification, proposal PDF | Transition matrix, immutable history, verifier scope, protected PDF, notification tests lulus. |
| 7 | Website BUMDes dan company profile PDF | Configurable domain, publication rules, responsive public site, printable PDF, tests lulus. |
| 8 | Map/geospatial | Admin editor, public layers, spatial/fallback storage, lightweight endpoint, tests lulus. |
| 9 | Dashboard tiga level | Aggregate benar, scoped per role, query/index profile memadai, tests lulus. |
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
- `php artisan test --compact`: 22 test lulus, 82 assertion.
- `vendor/bin/pint --format agent`: source PHP diformat (opsi `--dirty` tidak tersedia karena folder bukan Git repository).
- `npm run build`: production bundle berhasil; npm audit 0 vulnerability.
- Browser desktop: login, dashboard, pengguna, dan master wilayah dirender tanpa console error.
- Browser responsive: 1440×900, 1280×800, 1024×768, 768×1024, 390×844, dan 360×800; tidak ada page-level horizontal overflow.
- Mobile drawer: buka/tutup, focus, overlay, dan Escape-to-close berfungsi; tabel lebar scroll di container lokal.
- Log backend: tidak ada error baru setelah build dan browser verification. Entri error terakhir berasal dari iterasi test sebelum manifest tersedia.

## 16. Gate Sebelum Phase 2

1. finalisasi cardinality assignment Admin Kawasan pada AUT-F-004/KWS-F-004;
2. konfirmasi sumber dan cakupan seed data wilayah resmi—seed saat ini jelas berstatus demo;
3. review model kategori multi-relasi yang menyelesaikan discrepancy KWS-F-002;
4. restore/init Git agar perubahan dapat direview dan dipulihkan; dan
5. pertahankan TBD lain pada phase pemiliknya, tanpa mengisi business rule permanen melalui asumsi.
