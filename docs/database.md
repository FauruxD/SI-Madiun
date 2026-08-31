# Database Phase 1

Status: migration dan seed terverifikasi pada MySQL 5.7.33, 31 Agustus 2026.

## Tabel domain

| Tabel | Fungsi | Constraint utama |
|---|---|---|
| `users` | Identitas, kredensial, role, status akun | email unik, username unik nullable, role/status terindeks |
| `kabupaten` | Master kabupaten | kode unik, nama terindeks |
| `kecamatan` | Master kecamatan | kode unik, FK kabupaten `RESTRICT`, index parent + nama |
| `desa` | Master desa | kode unik, FK kecamatan `RESTRICT`, index parent + nama |
| `user_desa` | Assignment Admin Desa | primary key gabungan user + desa, kedua FK `CASCADE` |
| `activity_logs` | Audit immutable | actor nullable `NULL ON DELETE`, index action/subject/waktu |

Tabel infrastruktur Laravel tetap digunakan: `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, dan `failed_jobs`.

## Relasi

```text
kabupaten 1 ── * kecamatan 1 ── * desa
                                     *
                                     │ user_desa
                                     *
                                    users 1 ── * activity_logs
```

Penghapusan parent wilayah ditolak bila child masih ada. Controller juga memberi pesan yang dapat dipahami sebelum database constraint menjadi fallback terakhir. Assignment terhapus otomatis ketika user atau desa yang tidak lagi dipakai dihapus.

## Activity log

`activity_logs` hanya memiliki `created_at`; record tidak memiliki `updated_at` agar jejak audit tidak diperlakukan sebagai data yang dapat direvisi. Metadata JSON tidak boleh memuat password, token, isi dokumen privat, atau secret. Subject memakai pasangan `subject_type` + `subject_id` agar audit dapat mencakup entity fase berikutnya.

## Database development dan test

- aplikasi lokal: `si_madiun`;
- automated test: `si_madiun_test`;
- collation: `utf8mb4_unicode_ci`;
- test memakai `LazilyRefreshDatabase` dan tidak menyentuh data aplikasi lokal.

MySQL dipilih karena driver `pdo_mysql` dan server Laragon tersedia serta SRS mengizinkannya. Keputusan ini tidak menetapkan storage geospasial Phase 8; geometry akan ditempatkan di belakang model/serializer yang dapat diarahkan ke PostGIS atau fallback terkontrol.
