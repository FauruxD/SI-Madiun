# Arsitektur Aplikasi

Status: Phase 1–10 core terimplementasi pada 21 September 2026; blocker/TBD dicatat di traceability matrix.

## Bentuk aplikasi

Aplikasi menggunakan modular monolith Laravel 13. Phase 1 sengaja tetap memakai komponen native Laravel tanpa package auth/RBAC tambahan agar kontrol akses dapat dilacak langsung dan dependency tetap kecil.

Alur request utama:

1. route memakai middleware `auth` dan, untuk area administrasi, `role`;
2. Form Request melakukan validasi sekaligus authorization operasi tulis;
3. controller mengorkestrasi HTTP dan redirect; workflow multi-tabel dijalankan focused Action dalam transaction;
4. Policy serta query scope membatasi object yang dapat dibaca/diubah;
5. Eloquent menyimpan perubahan dengan foreign key dan unique constraint;
6. `RecordActivity` menyimpan actor, aksi, subject, IP, dan metadata aman.

## Struktur aplikasi

- `app/UserRole.php`: enum `SUPER_ADMIN`, `ADMIN_KAWASAN`, dan `ADMIN_DESA`.
- `app/Models`: model akses/wilayah Phase 1 serta `KawasanPerdesaan`, `KategoriKawasan`, dan `DokumenKawasan`.
- `app/Http/Controllers/Auth`: login/logout dan reset kata sandi native Laravel.
- `app/Http/Controllers/Admin`: manajemen pengguna, master wilayah, Kawasan, kategori, dan dokumen hukum.
- `app/Http/Requests`: validasi dan authorization setiap form.
- `app/Policies`: policy object-level untuk akun dan hierarki wilayah.
- `app/Actions`: satu pintu audit log serta transaction boundary create/update Kawasan dan lifecycle file dokumen.
- `app/Services/DashboardSummaryService.php`: proyeksi metric tiga role, status proposal, dan batch rekap per kawasan tanpa tabel metric terpisah.
- `app/Services/PublicPortalService.php`: proyeksi beranda publik dari status publikasi domain yang sudah ada, dengan eager loading dan jumlah item terbatas.
- `app/Http/Middleware/AddSecurityHeaders.php`: baseline header keamanan untuk response HTML, JSON, dan file.
- `app/Listeners/RecordSuccessfulLogin.php`: audit login melalui event Laravel.
- `resources/views`: guest layout, admin shell, dashboard, profil, pengguna, wilayah, Kawasan, dan kategori.
- `tests/Feature`: acceptance, validation, RBAC, IDOR, dan audit behavior.

## Keputusan akses

- Satu akun memiliki tepat satu role karena SRS/ERD belum meminta multi-role.
- Assignment Desa disimpan many-to-many pada `user_desa`; ini mendukung satu Admin Desa menangani lebih dari satu desa tanpa menduplikasi akun.
- Assignment Kawasan memakai pola yang sama pada `user_kawasan`; cardinality maksimum belum ditetapkan SRS/PRD sehingga implementasi tidak mengarang batas satu-ke-satu.
- Detail Desa tidak memakai implicit binding global. Query `Desa::visibleTo($user)` dijalankan sebelum `findOrFail`, sehingga percobaan membuka desa di luar scope menghasilkan 404 dan tidak membocorkan keberadaan record.
- Detail Kawasan dan download dokumen menggunakan pola scope-before-lookup yang sama. Dokumen selalu dicari dari relationship Kawasan, bukan dari ID/path global.
- Menu role-aware hanya lapisan presentasi. Middleware, Form Request, Policy, dan query constraint tetap menjadi enforcement server-side.
- Super Admin tidak dapat menurunkan role atau menonaktifkan akunnya sendiri.

## Frontend

Blade dan Tailwind CSS 4 membentuk application shell berbasis grid/flex. Sidebar tetap pada desktop dan menjadi drawer pada tablet/mobile. Drawer mendukung focus, Escape-to-close, overlay, serta reduced motion. Tabel lebar memakai container scroll lokal agar halaman tidak overflow horizontal. Pencarian Desa anggota memanggil endpoint database terbatasi 30 hasil dan selalu menyertakan pilihan aktif, sehingga form tidak memuat seluruh master Desa untuk difilter di browser.

Dashboard tetap menjadi read model, bukan source of truth. Seluruh metric dihitung dari entity domain dengan scope role dan filter tahun. Agregasi BUMDes/Kawasan membaca pivot serta rencana aksi secara batch; completeness profil hanya indikator operasional tujuh field inti dan bukan skor resmi.

Portal publik memakai layout Blade/Tailwind terpisah dengan navigasi Beranda, Kawasan, Potensi Desa, BUMDes, Peta, dan Tentang. Homepage menggabungkan hero, pencarian, potensi, peta, Kawasan, etalase BUMDes, peluang kemitraan, dan proposal yang sudah Published. Query publik selalu menggunakan publication gate domain; halaman tidak menyimpulkan bahwa website aktif otomatis mempublikasikan Company Profile.

## Operasi lintas tabel Phase 2

- `CreateKawasanAction` dan `UpdateKawasanAction` menyatukan record Kawasan, desa, kategori, Admin Kawasan, dan activity log dalam database transaction.
- `StoreDokumenKawasanAction` menyimpan file dengan nama hash pada disk privat, lalu membuat metadata dan audit secara atomik; file dikompensasi bila transaksi database gagal.
- `DeleteDokumenKawasanAction` menghapus file dan metadata melalui satu workflow yang dapat diuji.
- Kategori adalah master database, bukan enum. Enum hanya dipakai untuk nilai stabil status Kawasan dan jenis dokumen.

## Batas setelah Phase 2

- Admin Kawasan hanya memiliki akses baca terhadap assignment; hak edit berikutnya tidak diberikan tanpa requirement SRS/PRD.
- `DRAFT` dan `DITETAPKAN` bukan workflow approval. Aktor pengajuan/persetujuan dan transisinya masih TBD.
- Batas upload default 10 MiB hanya nilai konfigurasi development, bukan business requirement final.
- Phase 8 memakai MySQL GeoJSON fallback: nullable geometri pada model existing, dedicated validation/action/policy, dan explicit MapFeatureService projection. Leaflet dimuat hanya pada halaman peta. Browser QA enam viewport, editor, popup, layer, role/scope, dan console telah lulus; PostGIS/spatial-query, pembagian editor final, dan UAT resmi masih TBD.

## Portal dan hardening Phase 10

- Route utama `/` adalah portal publik pada host aplikasi utama, sedangkan host website BUMDes aktif tetap ditangani resolver tenant yang sudah ada.
- Kawasan publik hanya berstatus `DITETAPKAN`; potensi dan E-Proposal hanya `PUBLISHED`; direktori/peluang BUMDes hanya berasal dari website aktif dan parent nonarsip.
- Lookup detail Kawasan dilakukan pada scope publik sebelum `findOrFail`, sehingga ID draft menghasilkan 404. Output Blade di-escape dan PDF publik tetap melewati publication gate serta rate limit.
- Middleware global menambahkan `nosniff`, `SAMEORIGIN`, referrer policy, permissions policy, dan cross-origin opener policy. Login existing tetap rate-limited; reset password dan download proposal publik mendapat limiter khusus.
- Session disiapkan encrypted, HTTP-only, dan SameSite Lax. Produksi HTTPS wajib mengaktifkan secure cookie melalui environment.
- Portal adalah read model; Phase 10 tidak menambah tabel atau mengubah state machine. Seeder demo idempotent hanya membuat data non-geospasial berprefiks `DEMO -` dan tidak membuat batas/titik palsu.
