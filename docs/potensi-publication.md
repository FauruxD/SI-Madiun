# Publication Potensi Desa — Phase 5

## Scope

Phase 5 menyediakan katalog publik dari aggregate `potensi_desa` tanpa membangun E-Proposal, verifikasi Admin Kawasan, histori review, notifikasi, atau Proposal PDF. Fitur-fitur tersebut tetap menjadi Phase 6.

## Status source of truth

`App\Enums\PotensiStatus` memiliki dua nilai:

- `DRAFT`: data internal, tidak tampil di route publik;
- `PUBLISHED`: data boleh tampil pada katalog publik dan detail publik.

`published_at` adalah metadata waktu publikasi, bukan pengganti status. Query publik wajib menggunakan scope model `PotensiDesa::published()`, termasuk endpoint gambar publik dan detail. Potensi non-published menghasilkan 404.

Pada Phase 5, hanya Super Admin dapat memilih status manual. Admin Desa dapat mengelola isi potensi di Desa assignment-nya, tetapi request mereka tidak dapat mengubah status. Ini memberi cara aman untuk menyiapkan katalog awal tanpa mengklaim workflow E-Proposal.

## Lifecycle dan audit

Saat record dibuat atau diubah menjadi `PUBLISHED`, `published_at` diisi sekali. Saat dikembalikan ke `DRAFT`, nilainya dikosongkan. Create, update, delete/arsip, upload/remove media, perubahan kategori, perubahan BUMDes, dan perubahan status dicatat melalui `RecordActivity`.

Potensi memakai soft delete. Media/file tidak dihapus otomatis ketika potensi diarsipkan agar relasi historis dapat dipertahankan saat Phase 6 menambahkan proposal.

## Compatibility Phase 6

Phase 6 menambahkan aggregate `eproposal` dan transition terotorisasi: `DIAJUKAN → PERLU_REVISI`, `DIAJUKAN → TERVERIFIKASI`, dan `TERVERIFIKASI → PUBLISHED`, dengan resubmit eksplisit `PERLU_REVISI → DIAJUKAN`. Publication mengubah `PotensiDesa` menjadi `PUBLISHED` pada transaksi yang sama; public contract tetap hanya membaca `PUBLISHED` dan tidak ada tabel aggregate publik baru.

Record `PUBLISHED` yang dibuat langsung pada Phase 5 diperlakukan sebagai data katalog awal/legacy. Tidak ada provenance historis yang ditambahkan karena SRS/PRD belum mensyaratkannya. Admin Desa tidak pernah dapat mengubah status publication melalui request generik; publish proposal dilakukan melalui E-Proposal. Super Admin hanya dapat mengubah status manual pada potensi tanpa proposal. Setelah proposal ada, status generik dilarang dan potensi tidak dapat diarsipkan agar proposal tetap dapat dibaca. Pengajuan potensi legacy Published ditolak, bukan otomatis di-unpublish; aturan proposal legacy, provenance, dan unpublish tetap TBD.

## Video dan lokasi

Video Phase 5 disimpan sebagai URL HTTP/HTTPS eksternal dan ditampilkan sebagai link aman dengan `target="_blank"` serta `rel="noopener noreferrer"`; tidak ada streaming/transcoding. ERD SRS tidak memuat `lokasi_potensi`, sehingga koordinat ditunda ke Phase 8 bersama desain geospatial.
