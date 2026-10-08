# E-Proposal Workflow — Phase 6

## Scope

E-Proposal adalah metadata workflow di atas satu `PotensiDesa`. Judul, kategori, desa, deskripsi, media, kebutuhan investasi, BUMDes, dan kawasan selalu dibaca dari aggregate potensi sehingga tidak ada salinan data yang dapat berbeda.

Satu potensi hanya memiliki satu E-Proposal (`UNIQUE eproposal.potensi_desa_id`). Jalur yang tersedia pada Phase 6 adalah pengajuan oleh Admin Desa, dengan preview data dan galeri nyata sebelum submit. SRS menyebut masyarakat, tetapi PRD belum menetapkan identitas, ownership, tracking, maupun notifikasi untuk masyarakat; public submission karena itu ditandai **TBD / BLOCKED BY BUSINESS RULE**.

Pengajuan hanya menerima potensi `DRAFT` tanpa proposal. **ASSUMPTION / TBD legacy:** potensi yang sudah `PUBLISHED` lewat katalog awal tidak boleh ditarik publikasinya secara otomatis atau masuk kembali ke status pengajuan. Pengajuan untuk data publikasi lama ditolak sampai aturan bisnisnya ditetapkan; data legacy tidak diubah.

## Status dan transisi

```text
DIAJUKAN
├── PERLU_REVISI ──(resubmit Admin Desa)──> DIAJUKAN
└── TERVERIFIKASI ──(publish reviewer)──> PUBLISHED
```

Status domain proposal hanya `DIAJUKAN`, `PERLU_REVISI`, `TERVERIFIKASI`, dan `PUBLISHED`. `PotensiStatus::DRAFT`/`PUBLISHED` tetap menjadi domain katalog terpisah.

- `DIAJUKAN → PERLU_REVISI`: Admin Kawasan (atau Super Admin) wajib mengisi catatan revisi.
- `DIAJUKAN → TERVERIFIKASI`: Admin Kawasan (atau Super Admin) memverifikasi data.
- `PERLU_REVISI → DIAJUKAN`: Admin Desa (atau Super Admin) mengirim ulang setelah memperbaiki data potensi/catatan pengaju. Ini adalah asumsi eksplisit karena PRD tidak merinci arah resubmit.
- `TERVERIFIKASI → PUBLISHED`: Admin Kawasan (atau Super Admin) menerbitkan proposal; waktu publish yang sama disimpan pada proposal dan potensi.
- Tidak ada unpublish, reject, cancel, archive, atau transition setelah `PUBLISHED` pada Phase 6.

Semua transition dipusatkan pada `EProposalWorkflowService`, menggunakan transaksi database dan `lockForUpdate()`. Controller tidak pernah menerima `status` arbitrer.

## Hak akses

- **Admin Desa**: melihat proposal pada Desa assignment sendiri, mengajukan potensi yang belum memiliki proposal, melihat catatan revisi, dan resubmit.
- **Admin Kawasan**: melihat queue/detail pada desa anggota kawasan sendiri, meminta revisi, memverifikasi, publish, dan mengunduh PDF Verified/Published.
- **Super Admin**: akses seluruh scope dan seluruh action workflow; status manual Potensi Phase 5 hanya tersedia pada potensi tanpa E-Proposal. Setelah ada proposal, request generik tidak menerima status dan action persistence juga menolak perubahan status.
- **Publik**: tidak melihat workflow internal; hanya detail katalog dan PDF proposal Published yang potensi terkaitnya juga Published.

Scope diterapkan pada query sebelum lookup model. Pembacaan/aksi workflow lintas Desa/Kawasan menghasilkan 404; pengajuan potensi di luar kewenangan gagal validasi tanpa membuat proposal. Queue review dan dropdown Desa mengikuti desa anggota kawasan yang ditugaskan, bukan pivot `user_desa`.

Potensi dengan E-Proposal tidak dapat diarsipkan, termasuk soft-delete, agar detail, histori, dan PDF tidak kehilangan sumber data. Delete diotorisasi ulang setelah lock potensi untuk menutup race dengan submission. Edit isi/media tetap mengikuti permission Potensi Phase 5; aturan membekukan data setelah verifikasi atau membuat snapshot PDF masih **TBD**, sehingga PDF membaca data terkini, bukan dokumen historis immutable.

## Review history dan activity log

Setiap pengajuan/transisi membuat baris `review_eproposal` dengan status sebelum/sesudah, aksi, actor, catatan, dan waktu. History adalah catatan domain yang tidak ditimpa. `activity_logs` tetap mencatat aktivitas user (`eproposal.submitted`, `eproposal.revision_requested`, `eproposal.resubmitted`, `eproposal.verified`, `eproposal.published`) untuk audit operasional.

## Notification

`EProposalStatusChanged` dikirim tepat sekali oleh workflow service melalui Laravel database channel kepada `submitted_by` pada pengajuan, revisi, resubmit, verifikasi, dan publish. Payload hanya berisi pesan, status, id proposal/potensi, dan link internal. Database channel berjalan sinkron dalam transaksi, sehingga rollback juga membatalkan notification. Header mengambil unread count dengan query aggregate di view composer; daftar E-Proposal menampilkan maksimal sepuluh unread notification terbaru. PATCH untuk menandai dibaca hanya mencari notification milik user yang login. Email, SMS, WhatsApp, dan push notification bukan bagian Phase 6.

## Proposal PDF

`ProposalPdfService` memakai `barryvdh/laravel-dompdf:^3.1.2` dan template terpisah `resources/views/pdf/eproposal.blade.php`. PDF hanya boleh dibuat untuk `TERVERIFIKASI` atau `PUBLISHED`, memuat kop placeholder, identitas, desa/kecamatan/kabupaten, kategori, deskripsi, data pendukung, media foto yang tersedia, kebutuhan investasi, BUMDes, kawasan, status, dan tanggal.

Logo resmi, format nomor surat, tanda tangan pejabat, QR verifikasi, dan snapshot historis belum didefinisikan SRS/PRD sehingga ditampilkan sebagai placeholder/TBD. PDF dibuat on-demand sebagai response download; tidak ada file PDF/snapshot permanen. Foto hanya dibaca dari `media/potensi/{id}/images/{filename}` pada Storage disk terkonfigurasi, dengan pemeriksaan ukuran dan konten JPEG/PNG/WebP, lalu diubah menjadi data URI. Path traversal, folder potensi lain, gambar rusak, SVG, dan media hilang dilewati dengan fallback galeri. Remote resource, PHP, dan JavaScript rendering dinonaktifkan eksplisit. Audit download admin ditulis setelah render berhasil.

## Publication contract

Publish proposal dijalankan atomic: lock potensi → lock proposal → validasi transition → update E-Proposal → update Potensi Desa menjadi `PUBLISHED` → simpan review history → activity log → notification → commit. Urutan lock potensi dahulu konsisten dengan submission/update/archive. Katalog publik Phase 5 tetap menggunakan `PotensiDesa::published()`, sehingga proposal Verified belum tampil publik dan proposal Published selalu menyinkronkan potensi terkait.
