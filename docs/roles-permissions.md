# Role dan Permission Phase 1

Enforcement selalu dilakukan di server. Visibilitas menu bukan kontrol keamanan.

| Kapabilitas | Super Admin | Admin Kawasan | Admin Desa |
|---|:---:|:---:|:---:|
| Login/logout dan reset password | Ya | Ya | Ya |
| Melihat dashboard role-aware | Ya | Ya | Ya |
| Mengubah profil sendiri | Ya | Ya | Ya |
| Mengelola akun dan status | Ya | Tidak | Tidak |
| Menetapkan Admin Desa ke desa | Ya | Tidak | Tidak |
| Mengelola master Kabupaten/Kecamatan/Desa | Ya | Tidak | Tidak |
| Melihat semua detail desa | Ya | Tidak | Tidak |
| Melihat desa yang ditugaskan | Ya | Tidak | Ya |
| Mengelola kawasan | Phase 2 | Phase 2 | Tidak |
| Melihat audit terbaru | Ya | Tidak | Tidak |

## Enforcement berlapis

- `auth`: seluruh workspace internal.
- `role:SUPER_ADMIN`: seluruh route `/admin/*`.
- Form Request: authorization operasi tulis dan validasi payload.
- Policy: object-level account/wilayah authorization.
- `Desa::visibleTo()`: scoping query untuk mencegah IDOR lintas-desa.
- database constraint: unique, foreign key, dan primary key assignment.

## Aturan akun

- Akun nonaktif selalu ditolak ketika login, walaupun password benar.
- Login menerima email atau username dan dibatasi lima percobaan per menit per identifier + IP.
- Password minimum 10 karakter, memuat huruf besar, huruf kecil, dan angka.
- Admin Desa wajib memiliki sedikitnya satu assignment Desa saat akun dibuat/diubah.
- Mengubah role Admin Desa menjadi role lain otomatis melepaskan assignment Desa yang tidak lagi relevan.
- Super Admin tidak dapat menurunkan role atau menonaktifkan akun sendiri.

## Bukti pengujian

`AuthorizationTest`, `UserManagementTest`, `WilayahManagementTest`, dan `ProfileTest` menguji akses positif, penolakan role, assignment, cross-desa 404, self-demotion, unique validation, serta pencatatan aktivitas.
