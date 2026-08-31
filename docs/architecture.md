# Arsitektur Aplikasi

Status: Phase 1 selesai pada 31 Agustus 2026.

## Bentuk aplikasi

Aplikasi menggunakan modular monolith Laravel 13. Phase 1 sengaja tetap memakai komponen native Laravel tanpa package auth/RBAC tambahan agar kontrol akses dapat dilacak langsung dan dependency tetap kecil.

Alur request utama:

1. route memakai middleware `auth` dan, untuk area administrasi, `role`;
2. Form Request melakukan validasi sekaligus authorization operasi tulis;
3. controller mengorkestrasi HTTP, transaction, dan redirect;
4. Policy serta query scope membatasi object yang dapat dibaca/diubah;
5. Eloquent menyimpan perubahan dengan foreign key dan unique constraint;
6. `RecordActivity` menyimpan actor, aksi, subject, IP, dan metadata aman.

## Struktur Phase 1

- `app/UserRole.php`: enum `SUPER_ADMIN`, `ADMIN_KAWASAN`, dan `ADMIN_DESA`.
- `app/Models`: `User`, `Kabupaten`, `Kecamatan`, `Desa`, dan `ActivityLog`.
- `app/Http/Controllers/Auth`: login/logout dan reset kata sandi native Laravel.
- `app/Http/Controllers/Admin`: manajemen pengguna serta master wilayah.
- `app/Http/Requests`: validasi dan authorization setiap form.
- `app/Policies`: policy object-level untuk akun dan hierarki wilayah.
- `app/Actions/RecordActivity.php`: satu pintu audit log lintas-controller/listener.
- `app/Listeners/RecordSuccessfulLogin.php`: audit login melalui event Laravel.
- `resources/views`: guest layout, admin shell, dashboard, profil, pengguna, dan wilayah.
- `tests/Feature`: acceptance, validation, RBAC, IDOR, dan audit behavior.

## Keputusan akses

- Satu akun memiliki tepat satu role karena SRS/ERD belum meminta multi-role.
- Assignment Desa disimpan many-to-many pada `user_desa`; ini mendukung satu Admin Desa menangani lebih dari satu desa tanpa menduplikasi akun.
- Detail Desa tidak memakai implicit binding global. Query `Desa::visibleTo($user)` dijalankan sebelum `findOrFail`, sehingga percobaan membuka desa di luar scope menghasilkan 404 dan tidak membocorkan keberadaan record.
- Menu role-aware hanya lapisan presentasi. Middleware, Form Request, Policy, dan query constraint tetap menjadi enforcement server-side.
- Super Admin tidak dapat menurunkan role atau menonaktifkan akunnya sendiri.

## Frontend

Blade dan Tailwind CSS 4 membentuk application shell berbasis grid/flex. Sidebar tetap pada desktop dan menjadi drawer pada tablet/mobile. Drawer mendukung focus, Escape-to-close, overlay, serta reduced motion. Tabel lebar memakai container scroll lokal agar halaman tidak overflow horizontal.

## Batas Phase 1

- Entitas Kawasan dan `user_kawasan` baru dibuat pada Phase 2. Karena itu AUT-F-004 masih `IN PROGRESS`, walaupun assignment dan isolasi Desa sudah diuji.
- Admin Kawasan sudah dapat login dan menerima dashboard role-aware, tetapi belum memiliki object kawasan untuk dikelola.
- Abstraksi data geospasial belum dipilih. MySQL dipakai untuk fondasi lokal; Phase 8 harus menetapkan PostGIS atau format fallback tanpa mengubah kontrak domain Phase 1.
