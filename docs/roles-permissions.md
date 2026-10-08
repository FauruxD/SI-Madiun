# Role dan Permission Phase 1–10

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
| Melihat semua kawasan | Ya | Tidak | Tidak |
| Membuat/mengubah/menghapus kawasan | Ya | Tidak | Tidak |
| Mengelola kategori, desa anggota, dan assignment Admin Kawasan | Ya | Tidak | Tidak |
| Mengelola dokumen dasar hukum | Ya | Tidak | Tidak |
| Melihat kawasan yang ditugaskan | Ya | Ya (read-only) | Tidak |
| Mengunduh dokumen kawasan yang dapat dilihat | Ya | Ya | Tidak |
| Melihat profil BUMDes/BUMDesma | Semua | Desa dalam Kawasan yang ditugaskan | Desa yang ditugaskan |
| Melihat Potensi Desa | Semua | Desa dalam Kawasan yang ditugaskan (read-only) | Desa yang ditugaskan |
| Membuat/mengubah/mengarsipkan Potensi Desa | Ya | Tidak | Potensi pada Desa sendiri |
| Mengelola media Potensi Desa | Ya | Tidak | Potensi pada Desa sendiri |
| Mengelola master Kategori Potensi | Ya | Tidak | Tidak |
| Mengubah status katalog Phase 5 | Ya | Tidak | Tidak; status dipertahankan |
| Membuat BUMDes/BUMDesma | Ya | Tidak | Tidak |
| Mengubah profil dan child data BUMDes | Ya | Tidak | BUMDes pada Desa sendiri |
| Menghapus/mengarsipkan BUMDes | Ya | Tidak | Tidak |
| Mengelola dokumen/media privat BUMDes | Ya | Tidak | BUMDes pada Desa sendiri |
| Mengunduh dokumen/media BUMDes yang dapat dilihat | Ya | Ya (read-only) | Ya, pada Desa sendiri |
| Mengelola alamat dan aktivasi website BUMDes | Ya | Tidak | BUMDes pada Desa sendiri |
| Mengunduh Company Profile internal | Ya | Tidak | BUMDes pada Desa sendiri |
| Melihat rencana aksi dan realisasi | Semua | BUMDes dalam Kawasan yang ditugaskan | BUMDes pada Desa sendiri |
| Membuat/mengubah rencana aksi | Ya | Tidak | BUMDes pada Desa sendiri |
| Mengisi/mengubah realisasi berkala | Ya | Tidak | BUMDes pada Desa sendiri |
| Menghapus rencana tanpa realisasi | Ya | Tidak | BUMDes pada Desa sendiri |
| Membaca agregasi capaian BUMDes | Semua yang dapat melihat BUMDes | BUMDes dalam Kawasan yang ditugaskan | BUMDes pada Desa sendiri |
| Membaca agregasi Kawasan/PMD | Semua; PMD khusus Super Admin | Kawasan yang ditugaskan | Tidak |
| Melihat audit terbaru | Ya | Tidak | Tidak |

## Enforcement berlapis

- `auth`: seluruh workspace internal.
- `role:SUPER_ADMIN`: seluruh route `/admin/*`.
- Form Request: authorization operasi tulis dan validasi payload.
- Policy: object-level account, wilayah, Kawasan, kategori, dan dokumen authorization.
- `Desa::visibleTo()`: scoping query untuk mencegah IDOR lintas-desa.
- `KawasanPerdesaan::visibleTo()`: query assignment dijalankan sebelum `findOrFail`, sehingga ID Kawasan/dokumen di luar scope menghasilkan 404.
- `Bumdes::visibleTo()`: scope diturunkan dari `user_desa` untuk Admin Desa dan dari desa anggota Kawasan pada `user_kawasan` untuk Admin Kawasan.
- `RencanaAksi::visibleTo()`: scope selalu diturunkan melalui BUMDes yang terlihat; parent BUMDes dicari dahulu, lalu rencana dan realisasi dicari melalui relationship parent.
- Semua endpoint child BUMDes mencari parent yang terlihat lebih dahulu, kemudian child melalui relationship parent; ID milik parent lain menghasilkan 404.
- database constraint: unique, foreign key, dan primary key assignment.
- `PotensiDesa::visibleTo()` serta parent-scoped media lookup memastikan ID lintas Desa/Kawasan menghasilkan 404.

## Aturan akun

- Akun nonaktif selalu ditolak ketika login, walaupun password benar.
- Login menerima email atau username dan dibatasi lima percobaan per menit per identifier + IP.
- Password minimum 10 karakter, memuat huruf besar, huruf kecil, dan angka.
- Admin Desa wajib memiliki sedikitnya satu assignment Desa saat akun dibuat/diubah.
- Mengubah role Admin Desa menjadi role lain otomatis melepaskan assignment Desa yang tidak lagi relevan.
- Mengubah role Admin Kawasan menjadi role lain otomatis melepaskan assignment Kawasan yang tidak lagi valid dan mencatat aktivitas.
- Super Admin tidak dapat menurunkan role atau menonaktifkan akun sendiri.

## Aturan Kawasan

- Seluruh mutation Phase 2 berada di `/admin/*` dengan middleware `auth` + `role:SUPER_ADMIN` dan tetap diotorisasi ulang oleh Form Request/Policy.
- Admin Kawasan hanya mendapat detail read-only untuk Kawasan pada `user_kawasan`; tidak ada hak edit tersirat pada Phase 2.
- Admin Desa dan publik tidak dapat mengakses management atau dokumen Kawasan.
- Route unduh tidak menerima path file dari user; controller mencari dokumen melalui relasi Kawasan yang sudah di-scope.

## Aturan BUMDes/BUMDesma

### Phase 7 — Website dan Company Profile

- Admin Desa mengelola alamat/aktivasi website dan mengunduh Company Profile hanya untuk BUMDes milik Desa assignment-nya; Super Admin seluruh wilayah. Admin Kawasan tetap read-only dan tidak diberi hak download Company Profile, sesuai BUM-F-017.
- Query visibleTo dijalankan sebelum lookup pada settings/update/download. Public hanya active website dari parent non-archived, produk aktif pada unit aktif, nama/jabatan pengurus aktif, dan foto galeri/produk tervalidasi. Kontak/foto privat pengurus, dokumen legalitas, aset, serta keuangan tidak disajikan pada website.
- Host website hanya menerima GET/HEAD homepage dan media public-scoped; login/admin/mutation ditolak. Unknown dan unverified host ditolak sebelum web session/auth berjalan. Host aplikasi utama berasal dari APP_URL; localhost/127.0.0.1 hanya alias development.
- Company Profile admin berisi aset/keuangan sesuai BUM-F-016, tidak disamakan dengan website active. Public download tetap 404 karena SRS/PRD belum menetapkan otoritas/status published dan batas data keuangan publik.
- Pengelola hosting menangani verifikasi kepemilikan domain, DNS wildcard/custom, allowlist domain terverifikasi, serta TLS; tidak ada perubahan registrar/DNS otomatis.

### Aturan existing Phase 3

- Super Admin memiliki akses penuh dan menjadi satu-satunya role yang boleh mengarsipkan profil.
- Admin Desa hanya dapat membaca dan memperbarui BUMDes yang berelasi dengan Desa assignment-nya. Role ini tidak dapat membuat profil baru, mengubah jenis, atau memindahkan Desa pemilik lewat payload.
- Admin Kawasan hanya dapat membaca profil beserta child dan file privat untuk BUMDes yang memiliki Desa di Kawasan assignment-nya.
- Pengguna tanpa salah satu dari tiga role internal tidak memperoleh akses admin/public Phase 3.
- Daftar, detail, edit, unduh, dan seluruh mutation child tetap di-scope di server; menyembunyikan tombol bukan kontrol akses.
- Dokumen dan gambar menggunakan metadata database + file private; client tidak pernah mengirim path storage untuk diunduh.

## Aturan Rencana Aksi dan Realisasi

- Super Admin dapat membaca dan mengelola seluruh rencana/realisasi sebagai pengelola sistem.
- Admin Desa dapat membaca, membuat, dan memperbarui rencana/realisasi hanya bila BUMDes terkait dengan salah satu Desa assignment-nya.
- Admin Kawasan mendapat detail read-only untuk BUMDes yang berada pada Kawasan assignment-nya. Form realisasi tidak dirender dan mutation tetap ditolak Policy/Form Request.
- Rencana dari BUMDes lain dan realisasi dari rencana lain menghasilkan 404 melalui parent-scoped lookup, bukan bocoran keberadaan object.
- Rencana yang sudah memiliki realisasi tidak dapat dihapus. Perubahan status penting pada realisasi menghasilkan audit terpisah tanpa menyimpan catatan atau nilai lengkap ke metadata log.

## Aturan Potensi Desa dan Katalog

- Admin Desa hanya dapat membuat, membaca, mengubah, mengarsipkan, dan mengelola media untuk Potensi pada Desa assignment-nya. `desa_id` diambil melalui `Desa::visibleTo()` sebelum lookup record.
- Admin Kawasan hanya read-only untuk Potensi Desa anggota Kawasan yang ditugaskan; Phase 5 tidak memberikan hak verifikasi atau publikasi kepada role ini.
- Super Admin dapat mengelola seluruh Potensi, kategori, media, serta status manual `DRAFT`/`PUBLISHED` untuk seed katalog awal.
- BUMDes terkait wajib memiliki hubungan pada pivot `bumdes_desa` terhadap Desa Potensi; BUMDesma yang mencakup Desa tersebut valid.
- Endpoint publik selalu memakai `PotensiDesa::published()` di query database. Potensi draft/soft-deleted dan media child-nya menghasilkan 404, bukan bocoran status.
- Foto dan video child dicari melalui relationship parent yang sudah di-scope; path file tidak pernah diterima sebagai input download.

## E-Proposal dan Publikasi Phase 6

| Kemampuan | Super Admin | Admin Kawasan | Admin Desa | Publik |
|---|:---:|:---:|:---:|:---:|
| Melihat daftar/detail E-Proposal | Semua | Desa anggota kawasan | Potensi desa sendiri | Tidak |
| Mengajukan E-Proposal | Ya | Tidak | Ya, potensi sendiri | TBD |
| Meminta revisi | Ya | Ya, kawasan sendiri | Tidak | Tidak |
| Mengirim ulang setelah revisi | Ya | Tidak | Ya, proposal sendiri | Tidak |
| Verifikasi | Ya | Ya, kawasan sendiri | Tidak | Tidak |
| Publish proposal terverifikasi | Ya | Ya, kawasan sendiri | Tidak | Tidak |
| Unduh PDF admin | Semua proposal downloadable | Wilayah sendiri, status Verified/Published | Tidak diberikan | — |
| Unduh PDF publik | — | — | — | Hanya Published dan potensi Published |

Semua action workflow melewati `EProposalPolicy` dan `EProposalWorkflowService`; menyembunyikan tombol saja bukan boundary keamanan. Review queue khusus Super Admin/Admin Kawasan. Status manual hanya tersedia pada potensi tanpa E-Proposal; potensi berproposal tidak dapat diarsipkan. Pengajuan hanya untuk potensi Draft tanpa proposal, tanpa menarik publikasi legacy otomatis. Notification hanya dapat ditandai dibaca oleh akun penerimanya. Jalur masyarakat masih TBD karena SRS/PRD belum menetapkan identitas, ownership, tracking, dan channel notifikasi.

## Peta dan Geospasial Phase 8

| Kemampuan | Super Admin | Admin Kawasan | Admin Desa | Publik |
|---|:---:|:---:|:---:|:---:|
| Edit batas Kawasan | Semua | Kawasan assignment | Tidak; perlu pembagian editor final | Tidak |
| Edit kantor BUMDes / titik Potensi | Semua | Dalam Kawasan assignment | Dalam Desa assignment | Tidak |
| Tambah/edit/nonaktifkan titik informasi | Semua | Kawasan assignment | Tidak; scope fasilitas kawasan belum ditetapkan | Tidak |
| Lihat peta public | Ya | Ya | Ya | Read-only |

`manageMap` tidak mengubah `update`, website/PDF, profile, atau publication policy. Lookup memakai visibleTo sebelum findOrFail; point informasi dicari melalui parent Kawasan. Geometry write dan audit satu transaksi. Public projection menyembunyikan kawasan draft, potensi non-Published, archives, informasi nonaktif, dan private fields. Website aktif hanya membuka link website, bukan public PDF.

Pembagian akses di atas adalah scope konservatif sesuai uraian Admin Kawasan SRS halaman 7 dan existing ownership. QA interaksi/viewport telah lulus; PRD 20.17 dan UAT resmi belum ditutup.

61 test Phase 8 / 266 assertion lulus, termasuk matriks policy dan HTTP mutation-denied/IDOR. Final full suite Phase 1–8: 319 test / 1158 assertion lulus pada 19 September 2026. Fixture tes E-Proposal lama dibuat deterministik dengan persetujuan pengguna, tanpa perubahan authorization aplikasi. QA browser membuktikan Super Admin dapat mengelola semua jenis geometri, Admin Kawasan dapat mengelola poligon/titik informasi kawasan tugasnya, Admin Desa dapat mengelola titik BUMDes/Potensi desanya, dan akses Admin Desa ke poligon kawasan menghasilkan 404. Pembagian final editor Admin Desa tetap TBD.

## Portal Publik Phase 10

| Data | Syarat tampil publik |
|---|---|
| Kawasan | status `DITETAPKAN` dan tidak diarsipkan |
| Potensi Desa | status `PUBLISHED` dan tidak diarsipkan |
| Website/direktori BUMDes | website aktif dan parent BUMDes tidak diarsipkan |
| Peluang kemitraan | status `DIBUTUHKAN` pada BUMDes dengan website aktif |
| E-Proposal/PDF | proposal `PUBLISHED` dan Potensi terkait `PUBLISHED` |
| Company Profile PDF | tetap 404 publik sampai otoritas/state dan financial disclosure ditetapkan |

Pengunjung tidak memperoleh mutation route. Pengguna internal yang sedang login tetap melihat portal di `/` dan mendapat tautan kembali ke workspace; autentikasi tidak memperluas data yang ditampilkan oleh public projection.

## Bukti pengujian

Suite penuh Phase 1–10 lulus 334 test / 1251 assertion pada 21 September 2026. Phase 10 menambah bukti untuk publication gate portal, draft/inactive exclusion, public IDOR 404, escaping, bounded query count, security headers, CSRF, throttle PDF, dan idempotensi seeder. Public Company Profile dan jalur pengajuan masyarakat tetap blocked/TBD.
