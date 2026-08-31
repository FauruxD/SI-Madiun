# Requirement Traceability Matrix

Tanggal audit: 31 Agustus 2026  
Baseline: SRS 26 Agustus 2026 + PRD 0.1  
Current coverage: **5/50 TESTED; 1/50 IN PROGRESS; 44/50 belum dimulai atau masih blocked/TBD**

## Aturan Status

- `NOT STARTED`: requirement terverifikasi, tetapi belum ada implementasi domain.
- `IN PROGRESS`: implementasi sudah dimulai tetapi acceptance belum lengkap.
- `IMPLEMENTED`: behavior selesai, tetapi bukti automated test belum memadai.
- `TESTED`: implementation dan automated acceptance/security test relevan tersedia serta lulus.
- `BLOCKED/TBD`: requirement tidak dapat difinalkan tanpa keputusan bisnis/teknis.

Scaffold Laravel tidak dihitung sebagai implementasi. Path hanya dicantumkan setelah file behavior/test benar-benar ada.

| SRS ID | Requirement | Implementation | Test | Status |
|---|---|---|---|---|
| AUT-F-001 | Login Multi-Role | `LoginRequest`, `AuthenticatedSessionController`, `UserRole`, auth views, role-aware `DashboardController` | `AuthenticationTest`: email/username, inactive, invalid/rate-limit, logout; `AuthorizationTest`: dashboard tiga role | TESTED |
| AUT-F-002 | Hak Akses Berjenjang / RBAC | middleware `role`, Policy user/wilayah, Form Request authorization, `Desa::visibleTo()` | `AuthorizationTest`: forbidden lintas-role dan cross-desa 404; `WilayahManagementTest`: mutation ditolak | TESTED |
| AUT-F-003 | Reset Password | `PasswordResetLinkController`, `NewPasswordController`, token broker native, dua auth views | `AuthenticationTest::test_password_reset_link_can_be_requested_and_used` | TESTED |
| AUT-F-004 | Penugasan Wilayah Kerja | `user_desa`, relasi User–Desa, form assignment, scoped detail Desa selesai; `user_kawasan` menunggu Phase 2 | `UserManagementTest`: required/sync/detach; `AuthorizationTest`: assigned vs cross-desa; cross-kawasan belum dapat diuji | IN PROGRESS |
| AUT-F-005 | Profil Pengguna | `ProfileController`, `ProfileUpdateRequest`, `profile/edit.blade.php` | `ProfileTest`: own update, unique email/username, activity audit | TESTED |
| AUT-F-006 | Log Aktivitas | `ActivityLog`, `RecordActivity`, listener login; controller auth/profile/user/wilayah mengaudit mutation penting | `AuthenticationTest`, `ProfileTest`, `UserManagementTest`, dan `WilayahManagementTest` memverifikasi actor/action | TESTED |
| BUM-F-001 | Input Profil & Legalitas | Belum ada modul BUMDes/BUMDesma; desain harus mendukung kelompok desa | Belum ada CRUD/multi-desa/ownership test | NOT STARTED |
| BUM-F-002 | Unggah Dokumen AD/ART | Belum ada private document storage/metadata/ringkasan | Belum ada upload, permission, dan storage test | NOT STARTED |
| BUM-F-003 | Struktur Organisasi | Belum ada pengurus/urutan/foto/kontak | Belum ada CRUD/ownership test | NOT STARTED |
| BUM-F-004 | Katalog Produk/Layanan | Belum ada unit usaha dan produk/layanan | Belum ada relation/CRUD/public-scope test | NOT STARTED |
| BUM-F-005 | Manajemen Data Aset | Belum ada model/CRUD aset | Belum ada validation/ownership test | NOT STARTED |
| BUM-F-006 | Ringkasan Kinerja Keuangan | Belum ada data tahunan keuangan | Belum ada uniqueness/validation/privacy test | NOT STARTED |
| BUM-F-007 | Data Kemitraan | Belum ada kemitraan/kebutuhan investasi | Belum ada CRUD/publication test | NOT STARTED |
| BUM-F-008 | Input Rencana Kerja Tahunan | Belum ada `rencana_aksi` | Belum ada create/update/scope test | NOT STARTED |
| BUM-F-009 | Input Realisasi Berkala | Belum ada `realisasi_aksi` dan periode | Belum ada monthly/quarterly/yearly test | NOT STARTED |
| BUM-F-010 | Perhitungan Otomatis Capaian | Belum ada computation service; edge-case formula belum ditetapkan | Belum ada percentage/status/boundary test | BLOCKED/TBD |
| BUM-F-011 | Agregasi Capaian ke Dashboard | Belum ada aggregate query/service | Belum ada correctness/scope/query test | NOT STARTED |
| BUM-F-012 | Generate Website Otomatis | Belum ada public BUMDes projection/site | Belum ada active/public data test | NOT STARTED |
| BUM-F-013 | Pengaturan Subdomain | Belum ada configurable base-domain/subdomain routing | Belum ada uniqueness/host-routing test | NOT STARTED |
| BUM-F-014 | Opsi Custom Domain | Belum ada custom-domain setting | Belum ada uniqueness/host-resolution test | NOT STARTED |
| BUM-F-015 | Aktivasi/Nonaktivasi Website | Belum ada activation state/policy | Belum ada owner/public visibility test | NOT STARTED |
| BUM-F-016 | Generate Company Profile PDF | Belum ada PDF generator/template | Belum ada content/output test | NOT STARTED |
| BUM-F-017 | Unduh Dokumen - Admin | Belum ada authorized download | Belum ada Admin Desa/Super Admin permission test | NOT STARTED |
| BUM-F-018 | Unduh Dokumen - Publik | Publication mechanism Company Profile belum ditetapkan | Belum ada published/unpublished public test | BLOCKED/TBD |
| KAT-F-001 | Input Potensi Desa | Belum ada potensi/kategori/data investasi | Belum ada CRUD/validation/ownership test | NOT STARTED |
| KAT-F-002 | Galeri Foto & Video | Belum ada child media records/storage | Belum ada multi-media/upload test | NOT STARTED |
| KAT-F-003 | Tautan ke Profil BUMDes | Belum ada optional BUMDes relation/constraint desa | Belum ada same-desa/invalid-relation test | NOT STARTED |
| KAT-F-004 | Katalog Publik | Belum ada published scope/search/filter/pagination | Belum ada visibility/search/filter test | NOT STARTED |
| EPR-F-001 | Formulir Pengajuan E-Proposal | Belum ada form/submitter model; akses “masyarakat” belum diputuskan | Belum ada submitter/auth/validation test | BLOCKED/TBD |
| EPR-F-002 | Status Pengajuan Berjenjang | Belum ada state transition service; resubmission/edit rules belum lengkap | Belum ada valid/invalid transition/history test | BLOCKED/TBD |
| EPR-F-003 | Verifikasi oleh Admin Kawasan | Belum ada review action atau kawasan scope | Belum ada verifier/cross-kawasan test | NOT STARTED |
| EPR-F-004 | Publikasi Data | Belum ada publish guard/public projection | Belum ada verified-only/public visibility test | NOT STARTED |
| EPR-F-005 | Notifikasi Status | Belum ada Laravel database notification domain | Belum ada per-transition notification test | NOT STARTED |
| EPR-F-006 | Generate Proposal PDF | Belum ada status guard/PDF template | Belum ada verified/published/output test | NOT STARTED |
| EPR-F-007 | Unduh Dokumen - Admin | Belum ada authorized proposal download | Belum ada Admin Kawasan/Super Admin scope test | NOT STARTED |
| EPR-F-008 | Unduh Dokumen - Publik | Belum ada published-only public download | Belum ada published/unpublished test | NOT STARTED |
| KWS-F-001 | Pembuatan Kawasan Perdesaan | Belum ada Kawasan CRUD/desa assignment | Belum ada Super Admin/validation test | NOT STARTED |
| KWS-F-002 | Multi-Kategori per Kawasan | Belum ada master kategori/pivot; ERD konseptual bertentangan | Belum ada multi-category/integrity test | NOT STARTED |
| KWS-F-003 | Unggah Dasar Hukum | Belum ada private SK/TKPKP storage | Belum ada upload/download/authorization test | NOT STARTED |
| KWS-F-004 | Penugasan Admin Kawasan | Belum ada `user_kawasan`; menunggu entitas Kawasan Phase 2 | Belum ada assignment/cross-kawasan test | NOT STARTED |
| DSH-F-001 | Dashboard Desa/BUMDes | Belum ada dashboard/query scoped Desa | Belum ada metric/scope test | NOT STARTED |
| DSH-F-002 | Dashboard Kawasan | Belum ada dashboard/query scoped Kawasan | Belum ada metric/cross-kawasan test | NOT STARTED |
| DSH-F-003 | Dashboard PMD | Belum ada dashboard aggregate Kabupaten | Belum ada aggregate/permission test | NOT STARTED |
| PET-F-001 | Poligon Kawasan Perdesaan | Belum ada spatial polygon/layer; rule warna multi-kategori TBD | Belum ada geometry/public-layer test | NOT STARTED |
| PET-F-002 | Titik Kantor BUMDes | Belum ada spatial point/popup projection | Belum ada payload/link test | NOT STARTED |
| PET-F-003 | Titik Informasi Penting | Belum ada extensible point category | Belum ada category/layer test | NOT STARTED |
| PET-F-004 | Titik Potensi Desa | Belum ada spatial point relation ke potensi | Belum ada published/link test | NOT STARTED |
| PET-F-005 | Filter Layer Peta | Belum ada layer filter UI/endpoint | Belum ada filter/payload test | NOT STARTED |
| PET-F-006 | Input Data Peta oleh Admin | Belum ada draw/drop-pin editor; hak Admin Desa/Kawasan TBD | Belum ada edit-scope/validation test | BLOCKED/TBD |
| PET-F-007 | Mode Publik Read-Only | Belum ada public read-only map endpoint/UI | Belum ada public mutation-denied test | NOT STARTED |

## Discrepancy yang Mempengaruhi Traceability

- KWS-F-002 mengharuskan multi-kategori, tetapi ERD SRS menyimpan satu `kategori_tipologi`.
- BUM-F-001/glosarium mengharuskan dukungan kelompok desa untuk BUMDesma, tetapi ERD SRS menyimpan satu `id_desa`.
- Uraian Super Admin menyebut approval pembentukan BUMDes/Kawasan baru, tetapi tidak ada requirement ID, actor pengaju, state, atau acceptance criteria yang memadai.
- AUT-F-004 melintasi Phase 1 dan Phase 2 karena assignment Kawasan membutuhkan entitas Kawasan.

## Bukti Implementasi Phase 1

- Application routes: 35 route internal/auth/wilayah terdaftar.
- Domain tables: `kabupaten`, `kecamatan`, `desa`, `user_desa`, dan `activity_logs`; users memiliki role/status/profile.
- Test suite: 22 test lulus dengan 82 assertion pada MySQL test database terisolasi.
- Production build: Vite/Tailwind berhasil; npm audit tidak menemukan vulnerability.
- Browser: login, dashboard, pengguna, master wilayah, drawer keyboard/Escape, dan tabel mobile terverifikasi.
- Viewport: 1440×900, 1280×800, 1024×768, 768×1024, 390×844, dan 360×800 tanpa page-level horizontal overflow.

## Disiplin Pembaruan

1. Isi kolom Implementation dengan path migration/model/action/policy/controller/view nyata.
2. Isi kolom Test dengan path dan nama test nyata.
3. Gunakan `IMPLEMENTED` hanya jika acceptance behavior selesai tetapi bukti test belum memadai.
4. Gunakan `TESTED` hanya jika positive, validation, authorization, dan relevant failure paths lulus.
5. Pertahankan `BLOCKED/TBD` sampai keputusan dicatat; jangan isi business rule dengan asumsi permanen.
