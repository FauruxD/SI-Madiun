# Aturan Capaian Rencana Aksi

Dokumen ini merekam keputusan implementasi minimum Phase 4 untuk BUM-F-008 sampai BUM-F-011. SRS menetapkan fitur, formula dasar, dan empat status, tetapi tidak menetapkan seluruh semantik periodik.

## Periode

| Nilai domain | Label UI | `periode_ke` valid |
|---|---|---|
| `MONTHLY` | Bulanan | 1–12 (Januari–Desember) |
| `QUARTERLY` | Triwulanan | 1–4 (Triwulan 1–4) |
| `YEARLY` | Tahunan | 1 |

Validasi backend menolak nomor yang tidak sesuai. Unique constraint database `(rencana_aksi_id, periode_ke)` mencegah dua realisasi pada periode yang sama. Perubahan frekuensi rencana ditolak bila sudah ada nomor periode yang tidak valid pada frekuensi baru.

## Semantik nilai dan formula

Nilai `realisasi_nilai` adalah **tambahan pada periode tersebut (inkremental)**. Pengguna tidak memasukkan total kumulatif tahun berjalan.

```text
total_realisasi = jumlah realisasi_nilai seluruh periode
persentase_periode = realisasi_nilai_periode / target_nilai × 100
persentase_rencana = jumlah persentase_periode
                  = total_realisasi / target_nilai × 100
```

Perhitungan menggunakan `BigDecimal` dan pembulatan `HALF_UP` ke dua desimal. Tidak ada binary floating-point pada source of truth. Persentase tidak dibatasi ke 100%; target 100 dengan total realisasi 120 menghasilkan `120.00%`.

- `target_nilai > 0` dan realisasi numerik tersedia: persentase dihitung.
- target nol atau `NULL`: persentase `NULL`.
- realisasi numerik `NULL`: kontribusi persentase periode `NULL`, bukan nol buatan.
- realisasi numerik `0`: kontribusi `0.00%` yang valid.
- target deskriptif: tidak ada arbitrary scoring; status tetap dapat diisi operator.
- saat target numerik berubah, seluruh persentase periode dihitung ulang dalam transaction yang sama dengan perubahan rencana.

## Status progres

Status realisasi yang dapat dipilih operator adalah `IN_PROGRESS`, `COMPLETED`, atau `DELAYED`. Status rencana diturunkan secara terpusat:

1. belum ada realisasi → `NOT_STARTED` / Belum Mulai;
2. status periode bernomor paling akhir `DELAYED` → Tertunda;
3. status periode terakhir `COMPLETED`, atau persentase numerik total ≥ 100% → Selesai;
4. selain itu → `IN_PROGRESS` / Berjalan.

Status dan persentase sengaja dipisahkan. `DELAYED` adalah keputusan operator dan mendapat prioritas agar kondisi hambatan tidak disembunyikan oleh angka capaian.

## Agregasi

`BuildAchievementSummary` menyediakan tiga scope reusable: BUMDes, Kawasan, dan Kabupaten/PMD. Semua menerima filter tahun.

Output meliputi jumlah rencana, jumlah capaian valid, rata-rata capaian valid, serta jumlah Belum Mulai, Berjalan, Selesai, dan Tertunda. Rata-rata hanya memakai rencana dengan persentase numerik non-null; target deskriptif dan target nol tidak dimasukkan sebagai nol. Query mengambil aggregate per rencana dan status periode terakhir tanpa query per BUMDes.

Authorization tetap berada pada entry point service: BUMDes dan Kawasan dicari melalui scope actor, sedangkan ringkasan Kabupaten/PMD hanya dapat diminta Super Admin. UI Dashboard penuh tidak termasuk Phase 4.

## Penghapusan dan audit

Rencana tanpa realisasi dapat dihapus oleh role yang boleh mengubahnya. Rencana yang telah memiliki realisasi tidak dapat dihapus. Create/update/delete rencana, create/update realisasi, dan perubahan status realisasi dicatat oleh activity log tanpa memasukkan catatan atau nilai bisnis lengkap ke metadata.

## TBD / asumsi yang perlu dikonfirmasi

- SRS/PRD belum menetapkan inkremental versus kumulatif; Phase 4 memilih inkremental karena eksplisit, dapat dijumlahkan, dan mudah dimigrasikan.
- Belum ada aturan tenggat otomatis untuk `DELAYED`; status ini tetap manual.
- “Periode terakhir” saat ini berarti `periode_ke` terbesar yang telah diisi, bukan tanggal input terbaru.
- Form menerima tahun 2000 sampai sepuluh tahun setelah tahun berjalan sebagai guard kewajaran; horizon final menunggu keputusan PMD.
- Tidak ada weighted average antar-rencana. Setiap rencana dengan capaian valid mempunyai bobot yang sama.
