# Checklist UAT SI Kawasan Perdesaan Kabupaten Madiun

Status: **siap dijalankan, belum merupakan sign-off resmi**  
Baseline: SRS 26 Agustus 2026, PRD 0.1, dan implementasi Phase 1-10  
Lingkungan UAT: isi URL, versi commit, tanggal deployment, database fixture, dan peserta sebelum eksekusi.

## Aturan kelulusan

- Setiap skenario diberi `PASS`, `FAIL`, atau `BLOCKED` oleh pelaksana UAT.
- `PASS` membutuhkan hasil aktual dan bukti berupa screenshot, file hasil, atau referensi log.
- `FAIL` harus memiliki issue, severity, owner, dan hasil retest.
- `BLOCKED` dipakai hanya jika keputusan bisnis, data resmi, atau infrastruktur eksternal belum tersedia.
- Go-live membutuhkan seluruh skenario kritis PASS dan sign-off pemilik produk/DPMD. Automated test tidak menggantikan sign-off tersebut.

## Identitas pelaksanaan

| Field | Nilai |
|---|---|
| URL UAT | |
| Commit/release | |
| Tanggal | |
| Koordinator DPMD | |
| Perwakilan Admin Kawasan | |
| Perwakilan Admin Desa | |
| QA/Engineering | |
| Browser/perangkat | |

## Skenario inti

| ID | Aktor | Skenario dan langkah minimum | Hasil yang diharapkan | Status | Bukti/issue |
|---|---|---|---|---|---|
| UAT-01 | Semua role | Masuk dengan username/email, coba password salah, keluar | Login valid membuka dashboard sesuai role; login salah ditolak dan dibatasi; logout mengakhiri sesi | | |
| UAT-02 | Super Admin | Kelola akun, wilayah, kategori, Kawasan, assignment Desa/Kawasan | Perubahan tersimpan, validasi Indonesia tampil, dan activity log mencatat actor | | |
| UAT-03 | Admin Desa | Buka URL Desa/BUMDes/Potensi milik desa lain dan coba mutation langsung | Record luar scope tidak terlihat dan URL langsung menghasilkan 404/forbidden sesuai kontrak | | |
| UAT-04 | Admin Kawasan | Buka data Kawasan sendiri lalu coba URL Kawasan/desa di luar assignment | Data assignment tersedia read-only/review sesuai izin; data luar scope tidak terbuka | | |
| UAT-05 | Pengelola BUMDes | Kelola profil, legalitas, organisasi, produk, aset, keuangan, kemitraan, galeri | Parent-child benar, file tervalidasi, nilai decimal tersimpan, dan data privat tidak bocor ke publik | | |
| UAT-06 | Admin Desa | Buat rencana bulanan/triwulanan/tahunan dan isi realisasi | Slot periode benar, duplikasi ditolak, capaian/status dihitung sesuai data | | |
| UAT-07 | Admin Desa/Super Admin | Buat Potensi, unggah foto/video, ubah status melalui jalur yang diizinkan | Draft tidak tampil publik; Published tampil di katalog dan media dapat dibuka | | |
| UAT-08 | Admin Desa/Admin Kawasan/Super Admin | Ajukan, minta revisi, resubmit, verifikasi, publish, dan unduh E-Proposal | Hanya transisi valid yang berhasil; notifikasi, histori, PDF, dan katalog sinkron | | |
| UAT-09 | Admin Desa | Aktifkan/nonaktifkan website BUMDes dan buka host/path publik | Website aktif dapat dibuka, website nonaktif 404, produk/unit nonaktif serta kontak privat tidak tampil | | |
| UAT-10 | Admin + publik | Gambar batas/pin, simpan, filter empat layer, buka popup/list fallback | Geometri valid tersimpan sesuai scope; publik hanya-baca dan hanya memuat projection yang diizinkan | | |
| UAT-11 | Tiga role | Bandingkan dashboard terhadap data sumber dan ubah filter tahun | Metric sesuai scope/tahun; Admin Desa dan Kawasan tidak melihat wilayah lain | | |
| UAT-12 | Publik | Buka Beranda, Kawasan, Potensi Desa, BUMDes, Peta, Tentang; cari dan buka detail | Navigasi konsisten; draft/nonaktif tidak tampil; state kosong dan hasil pencarian dapat dipahami | | |
| UAT-13 | Admin + publik | Unduh dokumen privat, Proposal PDF publik, dan coba ID dokumen milik parent lain | Dokumen privat butuh login/izin; Proposal hanya Published; cross-parent ditolak; response file benar | | |
| UAT-14 | QA | Uji keyboard, skip link, focus, Escape drawer/popup, reduced motion, zoom 200 persen | Seluruh kontrol dapat dioperasikan, focus terlihat, urutan baca logis, dan tidak ada overlay menghalangi | | |
| UAT-15 | QA | Uji 1440x900, 1280x800, 1024x768, 768x1024, 390x844, 360x800 | Tidak ada overflow halaman; form, tabel lokal, peta, PDF link, dan navigasi tetap dapat digunakan | | |
| UAT-16 | Ops/Engineering | Verifikasi HTTPS, cookie secure, debug off, queue, cache, permission, backup/restore, DNS/TLS | Konfigurasi production aman; restore berhasil sesuai kebijakan; domain dan tile provider siap | | |

## Gate yang belum dapat diputuskan oleh implementasi

- Identitas dan tracking pengajuan E-Proposal oleh masyarakat.
- Workflow publikasi Company Profile PDF beserta klasifikasi data keuangan.
- Pembagian kewenangan final editor peta untuk Admin Desa.
- Data koordinat resmi, prioritas warna multi-kategori, serta keputusan PostGIS/spatial engine.
- SLA, target concurrent user, kapasitas file, backup retention, RPO/RTO, audit retention, dan accessibility conformance target.
- Format resmi kop, nomor, tanda tangan, QR, serta kebijakan unpublish PDF.

Item tersebut harus diputuskan pemilik sistem dan ditautkan ke notulen/issue sebelum status BLOCKED dapat menjadi PASS.

## Sign-off

| Peran | Nama | Keputusan | Tanggal | Tanda tangan/referensi digital |
|---|---|---|---|---|
| Product Owner/DPMD | | | | |
| Koordinator Admin Kawasan | | | | |
| Koordinator Admin Desa | | | | |
| QA | | | | |
| Engineering/Ops | | | | |
