# Requirement Traceability Matrix

Tanggal audit: 19 September 2026
Baseline: SRS 26 Agustus 2026 + PRD 0.1
Current coverage: **47/50 TESTED; 1/50 IN PROGRESS; 2/50 partial atau BLOCKED/TBD**

## Aturan Status

- `NOT STARTED`: requirement terverifikasi, tetapi belum ada implementasi domain.
- `IN PROGRESS`: implementasi sudah dimulai tetapi acceptance belum lengkap.
- `IMPLEMENTED`: behavior selesai, tetapi bukti automated test belum memadai.
- `TESTED`: implementation dan automated acceptance/security test relevan tersedia serta lulus.
- `BLOCKED/TBD`: requirement tidak dapat difinalkan tanpa keputusan bisnis/teknis.

Scaffold Laravel tidak dihitung sebagai implementasi. Path hanya dicantumkan setelah file behavior/test benar-benar ada.

| SRS ID | Requirement | Implementation | Tests | Status |
|---|---|---|---|---|
| AUT-F-001 | Login Multi-Role | `LoginRequest`, `AuthenticatedSessionController`, `UserRole`, auth views, role-aware `DashboardController` | `AuthenticationTest`: email/username, inactive, invalid/rate-limit, logout; `AuthorizationTest`: dashboard tiga role | TESTED |
| AUT-F-002 | Hak Akses Berjenjang / RBAC | middleware `role`, Policy user/wilayah, Form Request authorization, `Desa::visibleTo()` | `AuthorizationTest`: forbidden lintas-role dan cross-desa 404; `WilayahManagementTest`: mutation ditolak | TESTED |
| AUT-F-003 | Reset Password | `PasswordResetLinkController`, `NewPasswordController`, token broker native, dua auth views | `AuthenticationTest::test_password_reset_link_can_be_requested_and_used` | TESTED |
| AUT-F-004 | Penugasan Wilayah Kerja | `user_desa` dan `user_kawasan`, relasi scoped User–Desa/Kawasan, cleanup assignment saat role berubah | `UserManagementTest`, `AuthorizationTest`, `KawasanAuthorizationTest`: required/sync/detach serta cross-desa/kawasan 404 | TESTED |
| AUT-F-005 | Profil Pengguna | `ProfileController`, `ProfileUpdateRequest`, `profile/edit.blade.php` | `ProfileTest`: own update, unique email/username, activity audit | TESTED |
| AUT-F-006 | Log Aktivitas | `ActivityLog`, `RecordActivity`, listener login; controller auth/profile/user/wilayah mengaudit mutation penting | `AuthenticationTest`, `ProfileTest`, `UserManagementTest`, dan `WilayahManagementTest` memverifikasi actor/action | TESTED |
| BUM-F-001 | Input Profil & Legalitas | `Bumdes`, `bumdes_desa`, `BumdesController`, Store/Update Request + Action, `BumdesPolicy`, daftar/form/detail bertab | `BumdesProfileTest`, `BumdesPolicyTest`: CRUD, BUMDes satu Desa, BUMDesma multi-Desa, primary, filter, ownership, cross-scope | TESTED |
| BUM-F-002 | Unggah Dokumen AD/ART | `DokumenBumdes`, enum AD/ART/PERDES/PERKADES, private Storage, Store/Delete Action, authorized download, ringkasan terstruktur | `DokumenBumdesTest`: upload metadata/ringkasan, MIME/size, private download, IDOR, delete cleanup | TESTED |
| BUM-F-003 | Struktur Organisasi | `PengurusBumdes`, foto private, urutan/aktif, parent-scoped CRUD dan media delivery | `PengurusBumdesTest`: create/update/delete, activity log, child IDOR | TESTED |
| BUM-F-004 | Katalog Produk/Layanan | `UnitUsaha`, `ProdukLayanan`, status unit, foto private, nested CRUD | `UnitUsahaProdukTest`: relation, mutation, activity log, cross-parent IDOR | TESTED |
| BUM-F-005 | Manajemen Data Aset | `AsetBumdes`, decimal nilai perolehan, parent-scoped CRUD | `AsetBumdesTest`: create/update/delete, validation, unauthorized scope | TESTED |
| BUM-F-006 | Ringkasan Kinerja Keuangan | `KeuanganBumdes`, nominal decimal dan unique BUMDes+tahun, parent-scoped CRUD | `KeuanganBumdesTest`: annual CRUD, unique year, decimal/min validation, activity log | TESTED |
| BUM-F-007 | Data Kemitraan | `KemitraanBumdes`, status mitra berjalan/kebutuhan, nilai investasi, parent-scoped CRUD | `KemitraanBumdesTest`: create/update/delete, conditional partner validation, activity log | TESTED |
| BUM-F-008 | Input Rencana Kerja Tahunan | migration/model `RencanaAksi`, `ActionPeriod`, Policy, Store/Update Request, controller, matrix/form/detail Blade | `RencanaAksiTest`: create/update/delete-safe, validation, year filter, period enum, role/scope/IDOR, activity log | TESTED |
| BUM-F-009 | Input Realisasi Berkala | migration/model `RealisasiAksi`, unique rencana+periode, period-aware Form Request, `SaveActionRealization`, form slot bulanan/triwulanan/tahunan | `RealisasiAksiTest`: monthly/quarterly/yearly, duplicate aplikasi+DB, invalid period, read-only Kawasan, cross-parent IDOR, log | TESTED |
| BUM-F-010 | Perhitungan Otomatis Capaian | `ActionAchievementCalculator`, `RecalculateActionAchievements`, precise decimal, derived status dan target-null behavior | `ActionAchievementCalculatorTest` (12 boundary/status tests), `RealisasiAksiTest`: target update recalculation | TESTED |
| BUM-F-011 | Agregasi Capaian ke Dashboard | `BuildAchievementSummary`: reusable summary BUMDes/Kawasan/Kabupaten dengan year filter dan valid-only average | `AchievementAggregationTest`: counts/average, Kawasan membership, PMD, null exclusion, cross-kawasan scope | TESTED |
| BUM-F-012 | Generate Website Otomatis | `PublicBumdesWebsiteController`, Blade public projection dari data existing, produk/galeri paginated, tautan dari katalog Potensi | `BumdesWebsiteTest`: active-only projection, private/inactive exclusion, escaping, eager loading, pagination, katalog integration | TESTED |
| BUM-F-013 | Pengaturan Subdomain | `WebsiteBumdes`, default slug+ID, configurable base domain, `ResolveBumdesWebsiteHost` | `BumdesWebsiteTest`: creation/default uniqueness, validation, host resolution | TESTED (aplikasi; DNS/TLS produksi external dependency) |
| BUM-F-014 | Opsi Custom Domain | Domain setting unik/normalized; routing hanya domain yang diverifikasi pengelola hosting melalui konfigurasi server | `BumdesWebsiteTest`: uniqueness, invalid hostname, verified/unverified routing, host isolation | TESTED (aplikasi; registrar/DNS/TLS di luar aplikasi sesuai SRS) |
| BUM-F-015 | Aktivasi/Nonaktivasi Website | `BumdesWebsiteController`, Form Request/Policy, `SaveBumdesWebsiteAction`, default nonaktif, audit atomic | `BumdesWebsiteTest`: owner activate/deactivate, role/cross-desa guard, archive, audit rollback | TESTED |
| BUM-F-016 | Generate Company Profile PDF | `CompanyProfilePdfService`, `pdf.company-profile`, DomPDF existing; profil/organisasi/produk/aset/keuangan/galeri | `CompanyProfilePdfTest`: PDF/MIME/content sections, safe local images/fallback, escaping, disabled remote/PHP/JS | TESTED |
| BUM-F-017 | Unduh Dokumen - Admin | `CompanyProfileDownloadController@admin`, owner Admin Desa/Super Admin policy, no-store, audit sukses | `CompanyProfilePdfTest`, `BumdesWebsiteTest`: ownership, Super Admin, Kawasan forbidden, login redirect, permission matrix | TESTED |
| BUM-F-018 | Unduh Dokumen - Publik | Status published, penerbit, dan klasifikasi data keuangan belum ditetapkan SRS/PRD; endpoint fail-closed 404, tanpa tombol publik | `CompanyProfilePdfTest`: website aktif tidak otomatis mempublish PDF; tidak ada audit/download publik | BLOCKED/TBD (belum ada positive published-public flow) |
| KAT-F-001 | Input Potensi Desa | `KategoriPotensi`, `PotensiDesa`, CRUD controller/requests/action/policy, data investasi decimal, scoped admin UI | `KategoriPotensiTest`, `PotensiDesaTest`, `PotensiDesaPolicyTest` | TESTED |
| KAT-F-002 | Galeri Foto & Video | `MediaPotensi`, private Storage image action, external video URL action, ordered gallery and media endpoints | `MediaPotensiTest`, `PublicPotensiCatalogTest` | TESTED |
| KAT-F-003 | Tautan ke Profil BUMDes | nullable `potensi_desa.bumdes_id`, relation validation through `bumdes_desa` | `PotensiDesaTest`: same village, BUMDesma coverage, invalid relation, nullable | TESTED |
| KAT-F-004 | Katalog Publik | `published()` scope, public catalog/detail/media controllers, database-side search/category filter/pagination | `PublicPotensiCatalogTest`: published-only, detail 404, search, filter, pagination/query string, escaping | TESTED |
| EPR-F-001 | Formulir Pengajuan E-Proposal | `EProposal`, `EProposalController`, `StoreEProposalRequest`, scoped preview data/galeri; data proposal membaca aggregate `potensi_desa` tanpa duplikasi | `EProposalWorkflowTest`, `EProposalInterfaceTest`: submit own scope, preview, cross-desa, duplicate, initial status, timestamp, activity, escaping | PARTIAL / BLOCKED BY BUSINESS RULE (Admin Desa tested; masyarakat belum memiliki identitas/tracking resmi) |
| EPR-F-002 | Status Pengajuan Berjenjang | `EProposalStatus`, `EProposalWorkflowService`, `review_eproposal`, explicit transition guard termasuk resubmit `PERLU_REVISI → DIAJUKAN` | `EProposalWorkflowTest`: allowed/invalid transitions, revision note, history | TESTED |
| EPR-F-003 | Verifikasi oleh Admin Kawasan | `EProposalPolicy`, `EProposalReviewController`, scoped queue/detail, filter Desa/status/judul, workflow verify/revision | `EProposalWorkflowTest`, `EProposalInterfaceTest`, `EProposalPolicyTest`: queue/filter wilayah, cross-kawasan 404, Admin Desa forbidden, revision/verify | TESTED |
| EPR-F-004 | Publikasi Data | `EProposalWorkflowService::publish` mengubah E-Proposal dan `PotensiDesa` secara atomic; katalog Phase 5 tetap `published()` | `EProposalWorkflowTest`, `EProposalPdfTest`: publish sync, public catalog/link | TESTED |
| EPR-F-005 | Notifikasi Status | `EProposalStatusChanged`, Laravel database channel, satu trigger di workflow service, notification inbox/owned PATCH read | `EProposalWorkflowTest`, `EProposalInterfaceTest`: recipient/count dan cross-user notification 404 | TESTED |
| EPR-F-006 | Generate Proposal PDF | `ProposalPdfService`, `resources/views/pdf/eproposal.blade.php`, DomPDF `^3.1.2`, guard Verified/Published dan local-only image validation | `EProposalPdfTest`: output, MIME/content, status guard, embedded photo, missing/corrupt/arbitrary path fallback, escaped text, disabled remote/PHP/JS | TESTED |
| EPR-F-007 | Unduh Dokumen - Admin | `EProposalDownloadController@admin`, `download` policy dan territory scope Admin Kawasan/Super Admin | `EProposalPdfTest`: Admin Kawasan verified, Admin Desa forbidden; Super Admin covered by policy scope | TESTED |
| EPR-F-008 | Unduh Dokumen - Publik | `EProposalDownloadController@public`, `EProposal::published()` + `PotensiDesa::published()` | `EProposalPdfTest`: public Published allowed, Verified/unpublished 404, detail link | TESTED |
| KWS-F-001 | Pembuatan Kawasan Perdesaan | Migration/model `KawasanPerdesaan`, `kawasan_desa`, CRUD `Admin\KawasanController`, Form Request, transaction Action, Policy, Blade list/form/detail | `KawasanManagementTest`: list/create/update/validation/sync/unique/soft delete; `KawasanAuthorizationTest`; `KawasanPolicyTest` | TESTED |
| KWS-F-002 | Multi-Kategori per Kawasan | `KategoriKawasan`, master CRUD aktif/nonaktif, pivot `kawasan_kategori`, multi-select dan category filter | `KategoriKawasanManagementTest` dan `KawasanManagementTest`: multi-category, unique pair, kode unik, historical inactive relation | TESTED |
| KWS-F-003 | Unggah Dasar Hukum | `DokumenKawasan`, enum jenis, configurable private Storage, upload/delete Action, metadata, authorized scoped download | `DokumenKawasanTest`: SK/TKPKP, MIME, metadata, private download, delete cleanup | TESTED |
| KWS-F-004 | Penugasan Admin Kawasan | `user_kawasan`, role-constrained validation, transaction sync/audit, scoped read-only dashboard/detail Admin Kawasan | `KawasanManagementTest`, `KawasanAuthorizationTest`, `KawasanPolicyTest`: assignment, invalid role, detach, IDOR | TESTED |
| DSH-F-001 | Dashboard Desa/BUMDes | `DashboardSummaryService::forAdminDesa`, `DashboardController`, role section `dashboard.blade.php`; profil inti operasional, produk, rencana/realisasi, status proposal | `DashboardTest::test_admin_desa_dashboard_summarizes_only_assigned_villages` | TESTED |
| DSH-F-002 | Dashboard Kawasan | `DashboardSummaryService::forAdminKawasan` + batched `regionalData`, scoped `MapFeatureService`, rekap Kawasan/Desa/BUMDes/proposal/capaian dan peta | `DashboardTest`: scoped map/proposal/achievement lintas kawasan serta bounded query count | TESTED |
| DSH-F-003 | Dashboard PMD | `DashboardSummaryService::forSuperAdmin`, rekap Kabupaten dan per-kawasan, published metrics, valid-only average | `DashboardTest::test_super_admin_dashboard_reports_kabupaten_totals_and_per_region_recap` | TESTED |
| PET-F-001 | Poligon Kawasan Perdesaan | Nullable GeoJSON Polygon, MapFeatureService, Leaflet boundary/legend; dashed multi-kategori, semua kategori diproyeksikan | GeoJsonGeometryTest; PublicMapTest; browser draw/finish/koreksi simpul, multi-kategori, popup/legend | TESTED (GeoJSON fallback; warna prioritas bisnis tetap TBD) |
| PET-F-002 | Titik Kantor BUMDes | Point pada Bumdes, minimal popup projection, link website aktif, maksimal tiga nama unit aktif | PublicMapTest; browser drop/drag/save marker dan popup | TESTED |
| PET-F-003 | Titik Informasi Penting | TitikInformasi parent-scoped, jenis fasilitas/akses, aktif/nonaktif, dedicated action/request/controller | MapGeometryTest; PublicMapTest; browser create/deactivate dan public exclusion | TESTED |
| PET-F-004 | Titik Potensi Desa | PotensiDesa.geometri Point, public published projection/link katalog | PublicMapTest; MapGeometryTest; browser keyboard drop/save, marker, popup/link katalog | TESTED |
| PET-F-005 | Filter Layer Peta | map.js native layer groups dan empat checkbox; HTML fallback, counts dan truncation notice | PublicMapTest; browser empat toggle dan explicit empty state | TESTED |
| PET-F-006 | Input Data Peta oleh Admin | MapEditorScope, manageMap policies, SaveMapGeometry, draw/drag/drop-pin/keyboard editor; Desa hanya titik sendiri, batas Kawasan hanya Super/Kawasan | MapPolicyTest: full scope/role matrix; MapGeometryTest: auth/IDOR/validation/audit rollback; GeoJsonGeometryTest; browser role/scope/editor | IN PROGRESS (final pembagian editor Admin Desa pada PRD 20.17 dan UAT resmi TBD) |
| PET-F-007 | Mode Publik Read-Only | Public MapController index/data tanpa edit controls; semua write routes auth+scoped policy | MapGeometryTest; PublicMapTest; browser publik empat layer/popup/toggle tanpa edit control | TESTED |

## Discrepancy yang Mempengaruhi Traceability

- KWS-F-002 mengharuskan multi-kategori, tetapi ERD SRS menyimpan satu `kategori_tipologi`.
- BUM-F-001/glosarium mengharuskan dukungan kelompok desa untuk BUMDesma, tetapi ERD SRS menyimpan satu `id_desa`.
- Uraian Super Admin menyebut approval pembentukan BUMDes/Kawasan baru, tetapi tidak ada requirement ID, actor pengaju, state, atau acceptance criteria yang memadai.
- AUT-F-004 melintasi Phase 1 dan Phase 2; assignment Desa dan Kawasan kini selesai. Cardinality maksimum Admin–Kawasan masih tidak dibatasi karena SRS/PRD belum menetapkannya.
- SRS/PRD belum menetapkan apakah nilai realisasi periode bersifat inkremental atau kumulatif. Phase 4 memilih inkremental secara eksplisit dan mengisolasi kalkulasi agar dapat diubah tanpa mengubah kontrak HTTP/database.

## Bukti Implementasi Phase 1

- Application routes: 35 route internal/auth/wilayah terdaftar.
- Domain tables: `kabupaten`, `kecamatan`, `desa`, `user_desa`, dan `activity_logs`; users memiliki role/status/profile.
- Test suite Phase 1 setelah cleanup: 22 test lulus dengan 83 assertion pada MySQL test database terisolasi.
- Production build: Vite/Tailwind berhasil; npm audit tidak menemukan vulnerability.
- Browser: login, dashboard, pengguna, master wilayah, drawer keyboard/Escape, dan tabel mobile terverifikasi.
- Viewport: 1440×900, 1280×800, 1024×768, 768×1024, 390×844, dan 360×800 tanpa page-level horizontal overflow.

## Bukti Implementasi Phase 2

- Application routes: 52 route; mutation Kawasan dan endpoint pencarian Desa hanya berada di group Super Admin.
- Domain tables baru: `kawasan_perdesaan`, `kategori_kawasan`, `kawasan_desa`, `kawasan_kategori`, `user_kawasan`, dan `dokumen_kawasan`.
- Test suite penuh: 52 test lulus dengan 206 assertion; 30 test/123 assertion khusus Phase 2.
- Production build Vite/Tailwind, Composer validation/audit, dan npm audit berhasil.
- Pemeriksaan browser visual belum dapat diulang pada 1 September 2026 karena runtime kontrol browser lokal gagal diinisialisasi; automated HTTP tests dan production render compilation tetap lulus.

## Bukti Implementasi Phase 3

- Domain tables baru: `bumdes`, `bumdes_desa`, `dokumen_bumdes`, `pengurus_bumdes`, `unit_usaha`, `produk_layanan`, `aset_bumdes`, `keuangan_bumdes`, `kemitraan_bumdes`, dan `galeri_bumdes`.
- Sembilan detail tab merender data langsung dari database; daftar memakai server-side search/filter/pagination.
- 52 skenario khusus Phase 3 lulus dengan 164 assertion; full regression suite lulus 104 test/370 assertion.
- `migrate:fresh --seed` berhasil pada database test terisolasi, 85 route aplikasi terdaftar, dan production build/audit dependency lulus.
- Browser memverifikasi create/detail/tab child dan enam viewport tanpa page-level overflow, console error, atau error server baru.
- Traceability hanya dinaikkan untuk BUM-F-001–007. Galeri Phase 3 dicatat sebagai data pendukung BUMDes dan tidak mengklaim KAT-F-002 maupun publication requirement.
- KAT-F-001–004 dinaikkan menjadi TESTED setelah 38 Phase 5 tests dan full regression 182 tests lulus. Phase 6 menambah 34 test/159 assertion untuk E-Proposal, workflow, notification, PDF, UI/security, dan public integration; full regression Phase 1–6 mencapai 216 test/719 assertion. Jalur masyarakat pada EPR-F-001 tetap PARTIAL/BLOCKED sampai identitas dan tracking ditetapkan.

## Bukti Implementasi Phase 4

- Domain tables baru: `rencana_aksi` dan `realisasi_aksi`, dengan unique `(rencana_aksi_id, periode_ke)` dan nominal/persentase `DECIMAL`.
- Matrix memakai database-side search/year filter/pagination dan eager loading; service summary memakai aggregate relationship subquery lalu satu pass in-memory tanpa N+1.
- 40 skenario khusus Phase 4 lulus dengan 86 assertion dan full regression mencapai 144 test/456 assertion, mencakup seluruh minimum test prompt nomor 1–33.
- `migrate:fresh --seed`, 94 route, Blade cache, production build, dependency audit, serta smoke flow HTTP terautentikasi berhasil. Kontrol browser visual tidak tersedia pada host, sehingga enam viewport tidak diklaim terverifikasi ulang.
- Traceability hanya dinaikkan untuk BUM-F-008–011. Reusable aggregation tidak mengklaim DSH-F-001–003 karena Dashboard penuh tetap Phase 9.

## Bukti Phase 8 dan gate yang belum selesai

- GeoJSON fallback di MySQL mempertahankan profil/status/relasi existing; PostGIS/spatial-query tidak dibangun.
- Phase 8 memiliki 61 test / 266 assertion lulus secara focused, mencakup geometry, policy, HTTP scope/mutations, public projection, bounds/limits, dan audit rollback.
- Build/Pint/Blade cache/syntax JavaScript serta live public HTTP map/data lulus. Migration up pada utama dan down→up pada test kosong berhasil; tidak ada production seed geometry.
- Full regression awal 311 test / 1115 assertion lulus. Run berikutnya menemukan fixture lama EProposalInterfaceTest yang flaky: nama Faker “Desa Yos” menjadi substring desa assigned “Desa Yosodipuro”, bukan kebocoran query.
- Setelah izin eksplisit pengguna, hanya fixture nama desa tes tersebut dibuat deterministik; query aplikasi dan security assertions tidak diubah. EProposalInterfaceTest 11 test / 55 assertion lulus; final full regression Phase 1–8 319 test / 1158 assertion lulus.
- QA browser berhasil setelah fallback koneksi tab langsung: draw/finish/koreksi poligon, drop/drag/keyboard pin, titik informasi, popup, empat toggle/empty state, public read-only, serta role/scope diverifikasi. Peta publik dan editor lolos enam viewport tanpa page-level overflow; console tidak memiliki warning/error. Masalah popup tombol daftar ditemukan dan diperbaiki selama QA.
- Fixture browser Phase 8 hanya berada pada `si_madiun_test` dan telah dibersihkan oleh regression suite. Phase 10 kemudian menambahkan data non-geospasial berprefiks `DEMO -` secara eksplisit melalui seeder idempotent; database utama tetap tanpa geometri/titik dummy. PET-F-006 dipertahankan IN PROGRESS karena pembagian kewenangan editor Admin Desa dan UAT resmi belum final di sumber requirements.
- Data/batas resmi, UAT, final map editor authority, provider capacity produksi, dan pilihan spatial engine final masih TBD. Public Company Profile Phase 7 tetap blocked; Dashboard Phase 9 diverifikasi terpisah di bawah.

## Bukti Implementasi Phase 9

- Dashboard role-aware memproyeksikan ringkasan Desa/BUMDes, Kawasan, dan DPMD dari entitas domain; tidak ada tabel atau seed metric dashboard baru.
- Kelengkapan profil Desa adalah indikator operasional transparan `n/7` untuk tujuh isian inti dan tidak diklaim sebagai skor resmi SRS.
- Agregasi capaian memakai kembali `BuildAchievementSummary`; rekap banyak BUMDes/Kawasan dibaca secara batch. Tes enam kawasan membuktikan service selesai dalam paling banyak 12 query, bukan query per kartu.
- Lima skenario `DashboardTest` / 28 assertion lulus, mencakup metric, filter tahun, cross-desa/kawasan, peta scoped, proposal, capaian, dan batas query. Full regression Phase 1–9 lulus 324 test / 1186 assertion.
- Pint, Blade cache, dan production build Vite/Tailwind lulus. Browser memverifikasi tiga role, filter tahun, desktop 1440×900, tablet 768×1024, mobile 390×844, serta enam viewport target tanpa page-level horizontal overflow; console tidak memiliki warning/error.

## Bukti Implementasi Phase 10

- Portal publik menggabungkan seluruh prioritas homepage SRS/PRD dengan publication gate existing: Kawasan Ditetapkan, Potensi/E-Proposal Published, serta website BUMDes aktif. Tidak ada status bisnis baru yang diarang.
- `PublicPortalTest`, `SecurityHeadersTest`, `HomeRedirectTest`, dan `DatabaseSeederTest` membuktikan positive flow, draft/inactive exclusion, public IDOR 404, escaping, bounded query count, CSRF, security headers, PDF throttling, dan seeder idempotent.
- Focused suite Phase 10 lulus 11 test / 60 assertion; regression lintas auth/katalog/peta/website/PDF lulus 84 test / 400 assertion. Final full suite Phase 1–10 lulus 334 test / 1251 assertion pada 21 September 2026.
- Pint, Blade cache, production build, Composer/npm audit, seluruh migration, dan diff whitespace check lulus. Checklist UAT manual tersedia di `docs/uat-checklist.md`.
- Coverage requirement tetap **47/50 TESTED; 1/50 IN PROGRESS; 2/50 partial atau BLOCKED/TBD** karena Phase 10 tidak mengarang keputusan untuk Company Profile publik, pengajuan masyarakat, atau kewenangan final editor peta.

## Disiplin Pembaruan

1. Isi kolom Implementation dengan path migration/model/action/policy/controller/view nyata.
2. Isi kolom Test dengan path dan nama test nyata.
3. Gunakan `IMPLEMENTED` hanya jika acceptance behavior selesai tetapi bukti test belum memadai.
4. Gunakan `TESTED` hanya jika positive, validation, authorization, dan relevant failure paths lulus.
5. Pertahankan `BLOCKED/TBD` sampai keputusan dicatat; jangan isi business rule dengan asumsi permanen.
