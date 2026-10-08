# Database Phase 1–10

Status: seluruh migration terverifikasi pada MySQL 5.7.33, 21 September 2026. Seeder Phase 10 menyediakan data demo non-geospasial dan tidak memasukkan geometri dummy.

## Tabel domain

| Tabel | Fungsi | Constraint utama |
|---|---|---|
| `users` | Identitas, kredensial, role, status akun | email unik, username unik nullable, role/status terindeks |
| `kabupaten` | Master kabupaten | kode unik, nama terindeks |
| `kecamatan` | Master kecamatan | kode unik, FK kabupaten `RESTRICT`, index parent + nama |
| `desa` | Master desa | kode unik, FK kecamatan `RESTRICT`, index parent + nama |
| `user_desa` | Assignment Admin Desa | primary key gabungan user + desa, kedua FK `CASCADE` |
| `kawasan_perdesaan` | Identitas kawasan dan status penetapan | index status + nama, soft delete |
| `kategori_kawasan` | Master tipologi kawasan | kode unik, index aktif + nama |
| `kawasan_desa` | Desa anggota kawasan | ID, pasangan kawasan + desa unik, FK kawasan `CASCADE`, FK desa `RESTRICT` |
| `kawasan_kategori` | Multi-kategori kawasan | primary key gabungan, FK kawasan `CASCADE`, FK kategori `RESTRICT` |
| `user_kawasan` | Assignment Admin Kawasan | primary key gabungan user + kawasan, kedua FK `CASCADE` |
| `dokumen_kawasan` | Metadata SK Bupati/TKPKP | FK kawasan `CASCADE`, pengunggah `NULL ON DELETE`, index kawasan + jenis |
| `bumdes` | Profil dan legalitas inti BUMDes/BUMDesma | nomor registrasi unik nullable, jenis/nama/status terindeks, soft delete |
| `bumdes_desa` | Desa pemilik/anggota BUMDes/BUMDesma | ID pivot, pasangan BUMDes + Desa unik, penanda satu Desa utama pada aplikasi |
| `dokumen_bumdes` | Metadata AD/ART/PERDES/PERKADES dan ringkasan terstruktur | FK BUMDes `CASCADE`, pengunggah `NULL ON DELETE`, index BUMDes + jenis |
| `pengurus_bumdes` | Struktur organisasi dan urutan pengurus | FK BUMDes `CASCADE`, index aktif + urutan |
| `unit_usaha` | Unit usaha di bawah BUMDes | FK BUMDes `CASCADE`, index status + nama |
| `produk_layanan` | Produk/layanan per unit usaha | FK unit usaha `CASCADE`, index aktif + nama |
| `aset_bumdes` | Aset tetap dan nilai perolehan | FK BUMDes `CASCADE`, index nama aset |
| `keuangan_bumdes` | Ringkasan modal, omzet, SHU, dan kontribusi PADes tahunan | pasangan BUMDes + tahun unik |
| `kemitraan_bumdes` | Mitra berjalan dan kebutuhan investasi | FK BUMDes `CASCADE`, index status + jenis |
| `galeri_bumdes` | Metadata galeri gambar privat | tipe dibatasi aplikasi ke `IMAGE`, FK BUMDes `CASCADE` |
| `website_bumdes` | Konfigurasi one-to-one website BUMDes | FK BUMDes unik `RESTRICT`, subdomain unik, custom domain unik nullable, status aktif terindeks |
| `rencana_aksi` | Program, kegiatan, target, dan periode pemantauan tahunan BUMDes | FK BUMDes `RESTRICT`, index BUMDes+tahun dan tahun+periode |
| `realisasi_aksi` | Nilai, catatan, status, dan capaian tiap periode rencana | FK rencana `RESTRICT`, pasangan rencana+periode unik, index status+tanggal |
| `kategori_potensi` | Master klasifikasi katalog potensi | kode unik, index aktif + nama; dipakai/nonaktifkan tanpa hard delete |
| `potensi_desa` | Aggregate potensi desa dan publication source of truth Phase 5 | FK Desa/Kategori/BUMDes `RESTRICT`, status+published index, `DECIMAL(19,2)`, soft delete |
| `media_potensi` | Metadata foto privat dan referensi video eksternal | FK potensi `CASCADE`, tipe + urutan index, path/URL nullable sesuai tipe |
| `eproposal` | Metadata pengajuan dan state workflow satu potensi | FK potensi `RESTRICT` + unique 1:1, submitter nullable, status/tanggal indexes |
| `review_eproposal` | Histori immutable setiap transisi proposal | FK proposal `RESTRICT`, reviewer nullable `nullOnDelete`, status/action/time indexes |
| `notifications` | Notifikasi status internal Laravel | UUID primary key, morphic notifiable, payload data, read timestamp |
| `titik_informasi` | Titik fasilitas/akses pada Kawasan | FK Kawasan `RESTRICT`, pasangan kawasan+jenis+nama unik, status aktif terindeks |
| `activity_logs` | Audit immutable | actor nullable `NULL ON DELETE`, index action/subject/waktu |

Tabel infrastruktur Laravel tetap digunakan: `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, dan `failed_jobs`.

## Relasi

```text
kabupaten 1 ── * kecamatan 1 ── * desa
                                     * ── kawasan_desa ── * kawasan_perdesaan
                                     *                       *       *
                                     │ user_desa             │       ├── * dokumen_kawasan
                                     *                       │       │
                                    users * ─ user_kawasan ──*       *
                                      │                              │ kawasan_kategori
                                      └── 1 ── * activity_logs       *
                                                               kategori_kawasan
```

Penghapusan parent wilayah ditolak bila child masih ada. Desa yang menjadi anggota kawasan juga tidak dapat dihapus. Kawasan memakai soft delete agar record bisnis dan dokumen tidak hilang secara tidak sengaja; hard delete pada level database tetap tidak pernah menghapus master Desa atau Kategori. Pivot dan dokumen hanya `CASCADE` bila parent Kawasan benar-benar dihapus permanen.

Relasi Phase 3 adalah `desa * ── bumdes_desa ── * bumdes`. BUMDes memiliki banyak dokumen, pengurus, unit usaha, aset, data keuangan, kemitraan, dan gambar galeri; unit usaha memiliki banyak produk/layanan. Tidak ada `user_bumdes`: ownership Admin Desa diturunkan dari `user_desa`, sedangkan cakupan baca Admin Kawasan diturunkan dari desa anggota `user_kawasan`.

Relasi Phase 4 adalah `bumdes 1 ── * rencana_aksi 1 ── * realisasi_aksi`. Nilai realisasi disimpan sebagai baris per periode; tidak ada kolom Januari–Desember. Foreign key memakai `RESTRICT` dan aplikasi menolak penghapusan rencana yang telah memiliki realisasi agar riwayat capaian tidak hilang diam-diam.

Relasi Phase 5 adalah `desa 1 ── * potensi_desa`, `kategori_potensi 1 ── * potensi_desa`, dan optional `bumdes 1 ── * potensi_desa`. Relasi BUMDes divalidasi melalui pivot `bumdes_desa`, sehingga BUMDesma dapat mencakup lebih dari satu Desa. Setiap potensi memiliki `media_potensi` 1:N; cover list dipilih dari media IMAGE ber-`urutan` terendah. SRS ERD tidak memuat `lokasi_potensi`; koordinat kemudian diimplementasikan pada Phase 8 sebagai nullable GeoJSON `geometri`.

## Aturan Phase 2

- `status` dibatasi aplikasi ke `DRAFT` dan `DITETAPKAN`; SRS belum mendefinisikan approval workflow lain.
- `kawasan_desa` memungkinkan satu Desa berada pada beberapa Kawasan karena cardinality overlap belum ditetapkan; unique pair mencegah duplikasi pada Kawasan yang sama.
- `user_kawasan` mengikuti pola many-to-many `user_desa`. Form hanya menerima user ber-role `ADMIN_KAWASAN`.
- Kategori yang telah dipakai dinonaktifkan dengan `is_active`, bukan dihapus, sehingga relasi historis tetap utuh.
- Binary dokumen tidak masuk database. Metadata menyimpan path privat, nama asli yang dibersihkan, MIME, ukuran, tanggal/nomor, dan actor upload.

## Aturan Phase 3

- BUMDes biasa hanya boleh terkait ke satu Desa; BUMDesma boleh terkait ke beberapa Desa. Setiap profil wajib memilih tepat satu Desa utama, dan unique pair mencegah duplikasi relasi.
- SRS BUM-F-001 dan glosarium mengharuskan BUMDesma lintas desa sehingga pivot `bumdes_desa` dipakai meskipun ERD konseptual hanya menggambar satu `id_desa`.
- `status_hukum` tetap berupa string tervalidasi karena SRS belum menetapkan daftar status atau workflow approval yang final.
- Nominal memakai `DECIMAL(19,2)` dan bukan floating point. Data keuangan unik per BUMDes dan tahun.
- Dokumen legalitas, foto pengurus, foto produk, dan galeri disimpan pada disk private. Route unduh/media melakukan authorization lalu mencari child melalui relation parent yang sudah di-scope.
- Ringkasan dokumen menyimpan maksud/tujuan, modal awal, dan pembagian SHU sebagai field terstruktur; isi binary dokumen tidak disalin ke log atau database.
- Galeri Phase 3 hanya menerima gambar. Video dan publication state tetap di luar scope phase ini.

## Aturan Phase 4

- `periode` memakai nilai `MONTHLY`, `QUARTERLY`, atau `YEARLY`; `periode_ke` masing-masing dibatasi aplikasi ke 1–12, 1–4, atau 1. Constraint unik `(rencana_aksi_id, periode_ke)` tetap menjadi perlindungan terakhir terhadap duplikasi.
- `target_nilai` dan `realisasi_nilai` memakai `DECIMAL(19,2)`. `persentase_capaian` memakai `DECIMAL(30,2)` agar overachievement tidak dipotong ke 100% dan rasio nilai besar tetap dapat disimpan.
- Persentase pada setiap realisasi adalah kontribusi periode: `realisasi_nilai / target_nilai × 100`. Total rencana menjumlahkan realisasi inkremental dan kontribusi persentasenya.
- Target kosong/nol atau realisasi numerik kosong menghasilkan persentase `NULL`, bukan nol. Agregasi rata-rata hanya memakai rencana dengan capaian numerik valid.
- Status `NOT_STARTED` tidak disimpan sebagai input realisasi; status itu diturunkan saat rencana belum memiliki realisasi. Status input adalah `IN_PROGRESS`, `COMPLETED`, atau `DELAYED`.
- Ketentuan lengkap, edge case, dan asumsi yang masih TBD dicatat di `docs/action-plan-achievement.md`.

## Aturan Phase 5

- `PotensiStatus` hanya memiliki `DRAFT` dan `PUBLISHED` pada Phase 5. Super Admin dapat mengubah status secara manual untuk katalog awal; Admin Desa tidak dapat mengubah status lewat request.
- `published_at` diisi ketika status berubah menjadi `PUBLISHED` dan dikosongkan ketika kembali ke `DRAFT`. Scope `published()` menjadi satu-satunya query contract katalog publik.
- `PotensiDesa` memakai soft delete agar calon relasi E-Proposal Phase 6 tidak kehilangan aggregate historis. Media dan file tidak dihapus otomatis saat potensi diarsipkan.
- Foto disimpan pada private disk configurable di `media/potensi/{id}/images`; nama path dihasilkan Laravel Storage, sedangkan metadata MIME/ukuran/nama asli disimpan untuk response terkontrol. Video Phase 5 hanya menyimpan URL HTTP/HTTPS eksternal.
- Kategori yang digunakan tidak dihapus; Super Admin menonaktifkannya. Kategori nonaktif tidak dapat dipilih untuk potensi baru tetapi tetap tampil pada data historis.

## Aturan Phase 6 — E-Proposal

- `eproposal` memiliki relasi one-to-zero-or-one ke `potensi_desa` melalui `potensi_desa_id` yang `UNIQUE`. Proposal menyimpan state workflow dan metadata pengajuan, bukan salinan judul, deskripsi, kategori, media, atau BUMDes.
- Status E-Proposal dipisahkan dari `PotensiStatus`: `DIAJUKAN`, `PERLU_REVISI`, `TERVERIFIKASI`, dan `PUBLISHED`. Index status/tanggal, submitter, dan publication mendukung queue serta download.
- `review_eproposal` menyimpan setiap transisi beserta reviewer, status sebelum/sesudah, aksi, catatan, dan waktu review. FK reviewer nullable `nullOnDelete()` menjaga histori ketika akun dihapus; FK proposal `RESTRICT` mencegah orphan history.
- FK proposal ke potensi memakai `RESTRICT`; potensi tanpa proposal tetap memakai soft delete. Potensi berproposal tidak dapat diarsipkan oleh policy/controller agar aggregate historis tetap dapat dibaca. Media Phase 5 tidak dihapus otomatis saat potensi tanpa proposal diarsipkan.
- Transition dan publication dijalankan di `DB::transaction()` dengan `lockForUpdate()`. Publication mengubah `eproposal` dan `potensi_desa` pada transaksi yang sama.
- Migration `notifications` menggunakan database channel Laravel (`uuid` notification id, morphic notifiable, payload JSON text). Tidak ada channel eksternal yang diasumsikan.

## Aturan Phase 7 — Website BUMDes

- `website_bumdes` one-to-one dengan `bumdes`; FK `bumdes_id` unique dan `RESTRICT`. Unique `subdomain` (63 karakter) dan nullable unique `custom_domain` (253 karakter) menjaga mapping host.
- `is_active` default false, `activated_at` nullable. Migration membackfill alamat slug+ID secara chunked untuk profil existing tanpa mengubah data profil/publikasi sebelumnya. CreateBumdesAction membuat website nonaktif dalam transaksi create profil.
- Public active scope juga mensyaratkan parent BUMDes tidak diarsipkan. Menonaktifkan website menutup halaman dan media, bukan menghapus file private.
- Custom domain dinormalisasi dan baru routable jika ada dalam `BUMDES_VERIFIED_CUSTOM_DOMAINS` yang dikendalikan pengelola hosting; operator tidak dapat mengirim verification flag.
- Tidak ada publication field Company Profile yang diarang. Website active bukan status PDF published; BUM-F-018 tetap blocked sampai otoritas/state dan financial disclosure ditentukan.
- Company Profile dibuat on-demand, tanpa binary PDF, snapshot, atau nomor dokumen baru dalam database. Rollback migration menghapus konfigurasi website, sehingga destructive terhadap konfigurasi Phase 7; profil/domain Phase 1–6 tetap dipertahankan.

## Aturan Phase 8 — GeoJSON fallback

- `geometri` nullable JSON pada `kawasan_perdesaan`, `bumdes`, `potensi_desa`; array cast tanpa mass-assignment generic CRUD. Geometry disimpan melalui action dedicated: Polygon satu ring luar untuk Kawasan, Point untuk kantor BUMDes/Potensi.
- `titik_informasi` berisi parent `kawasan_id`, nama, jenis, keterangan singkat, Point GeoJSON, `is_active`, dan timestamps. Unique (kawasan_id, jenis, nama) mencegah duplikasi; FK parent RESTRICT. Penonaktifan tidak menghapus record.
- Validation menolak nonnumeric/nonfinite/out-of-range coordinates, geometry tambahan, ring terbuka/degenerate/bersilang, lebih dari 500 simpul, serta JSON string di atas 64 KiB. Enam desimal; holes/MultiPolygon/spatial-query tidak tersedia dalam fallback awal.
- Parent lock dan transaksi mencakup geometri/point serta activity log. Geometry dan nama/keterangan lengkap tidak disalin ke audit metadata.
- Migration up tidak mengubah field/status/relasi existing. Down menghapus tabel/kolom Phase 8, bukan tabel Phase 1–7; backup geometry diperlukan sebelum rollback berisi data. Down→up dibuktikan hanya pada `si_madiun_test` yang geometri dan titiknya kosong.
- Factory menyediakan fixture test. Seeder utama tidak mengisi koordinat/batas resmi karena sumber datanya belum diberikan. MySQL tetap digunakan; PostGIS dan spatial indexing tidak diklaim selesai.

## Activity log

`activity_logs` hanya memiliki `created_at`; record tidak memiliki `updated_at` agar jejak audit tidak diperlakukan sebagai data yang dapat direvisi. Metadata JSON tidak boleh memuat password, token, isi dokumen privat, atau secret. Subject memakai pasangan `subject_type` + `subject_id` agar audit dapat mencakup entity fase berikutnya.

## Database development dan test

- aplikasi lokal: `si_madiun`;
- automated test: `si_madiun_test`;
- collation: `utf8mb4_unicode_ci`;
- test memakai `LazilyRefreshDatabase` dan tidak menyentuh data aplikasi lokal.

MySQL dipilih karena driver `pdo_mysql` dan server Laragon tersedia serta SRS mengizinkannya. Phase 8 memakai GeoJSON fallback melalui model/action/serializer khusus; pilihan PostGIS/spatial engine final masih TBD.

## Seeder demo Phase 10

- `DemoDomainSeeder` bersifat idempotent dan membuat satu rangkaian demo Kawasan ditetapkan, BUMDes dengan website aktif, peluang kemitraan, Potensi Published, serta E-Proposal Published.
- Nama domain demo memakai awalan `DEMO -` atau nomor `DEMO/` agar mudah dibedakan dari data resmi.
- Seeder tidak membuat geometri, dokumen privat, data keuangan, atau publication state Company Profile yang belum memiliki aturan bisnis final.
- Automated test tetap menggunakan `si_madiun_test` dan `LazilyRefreshDatabase`; seeder demo diverifikasi dapat dijalankan dua kali tanpa duplikasi.
