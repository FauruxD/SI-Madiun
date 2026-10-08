<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Company Profile {{ $bumdes->nama }}</title>
<style>
    @page { margin: 38px 38px 52px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.6; color: #292524; }
    header { border-bottom: 2px solid #065f46; padding-bottom: 14px; margin-bottom: 20px; }
    .label { color: #065f46; font-size: 9px; letter-spacing: 1.5px; font-weight: bold; }
    h1 { font-family: DejaVu Serif, serif; font-size: 26px; line-height: 1.25; color: #064e3b; margin: 8px 0; }
    h2 { font-size: 13px; color: #065f46; margin: 22px 0 10px; page-break-after: avoid; }
    p { margin: 6px 0; } .muted { color: #78716c; } .pre { white-space: pre-line; }
    table { width: 100%; border-collapse: collapse; font-size: 9px; } thead { display: table-header-group; }
    th { text-align: left; background: #f5f5f4; color: #44403c; }
    th, td { border-bottom: 1px solid #d6d3d1; padding: 7px 6px; vertical-align: top; overflow-wrap: break-word; }
    tr { page-break-inside: avoid; } .identity td:first-child { width: 28%; color: #78716c; }
    .financial-summary { page-break-inside: avoid; }
    figure { margin: 12px 0; page-break-inside: avoid; } img { max-width: 260px; max-height: 180px; } figcaption { color: #78716c; font-size: 9px; }
    footer { position: fixed; bottom: -32px; left: 0; right: 0; border-top: 1px solid #d6d3d1; padding-top: 6px; font-size: 8px; color: #78716c; }
</style></head><body>
<footer>Data bersumber dari sistem. Format resmi logo, kop, nomor dokumen, tanda tangan, dan publikasi masih menunggu ketentuan DPMD.</footer>
<header><p class="label">BUMDES / BUMDESMA KABUPATEN MADIUN</p><h1>Company Profile</h1><p>{{ $bumdes->nama }}</p><p class="muted">Dokumen internal, dihasilkan {{ now()->translatedFormat('d F Y') }}. Bukan bukti publikasi.</p></header>
<table class="identity"><tbody>@foreach(['Jenis' => $bumdes->jenis->label(), 'Nomor registrasi' => $bumdes->nomor_registrasi ?: 'Belum diisi', 'Tahun berdiri' => $bumdes->tahun_berdiri ?: 'Belum diisi', 'Status hukum' => $bumdes->status_hukum ?: 'Belum diisi', 'Alamat' => $bumdes->alamat ?: 'Belum diisi'] as $label => $value)<tr><td>{{ $label }}</td><td>{{ $value }}</td></tr>@endforeach<tr><td>Desa asal</td><td>@forelse($bumdes->desas as $desa)<div>{{ $desa->nama }}, Kecamatan {{ $desa->kecamatan->nama }}, {{ $desa->kecamatan->kabupaten->nama }}{{ $desa->pivot->is_desa_utama ? ' (utama)' : '' }}</div>@empty Belum diisi @endforelse</td></tr></tbody></table>
<h2>Profil usaha</h2><p class="pre">{{ $bumdes->deskripsi ?: 'Deskripsi belum dilengkapi.' }}</p>
<h2>Struktur organisasi</h2><table><thead><tr><th>Nama</th><th>Jabatan</th></tr></thead><tbody>@forelse($bumdes->managers as $manager)<tr><td>{{ $manager->nama }}</td><td>{{ $manager->jabatan }}</td></tr>@empty<tr><td colspan="2">Belum ada pengurus aktif.</td></tr>@endforelse</tbody></table>
<h2>Unit usaha dan katalog produk / layanan</h2>@forelse($bumdes->businessUnits as $unit)<p><strong>{{ $unit->nama }}</strong> <span class="muted">({{ $unit->status->label() }})</span></p><p class="pre">{{ $unit->deskripsi }}</p><table><thead><tr><th>Produk / layanan</th><th>Deskripsi</th><th>Harga</th></tr></thead><tbody>@forelse($unit->products as $product)<tr><td>{{ $product->nama }}{{ $product->is_active ? '' : ' (nonaktif)' }}</td><td>{{ $product->deskripsi ?: 'Belum diisi' }}</td><td>{{ $product->harga !== null ? 'Rp '.Number::format($product->harga, locale: 'id').($product->satuan ? ' / '.$product->satuan : '') : 'Belum diisi' }}</td></tr>@empty<tr><td colspan="3">Belum ada produk / layanan.</td></tr>@endforelse</tbody></table>@empty<p>Belum ada unit usaha.</p>@endforelse
<h2>Data aset</h2><table><thead><tr><th>Aset</th><th>Jenis</th><th>Kepemilikan</th><th>Nilai perkiraan</th></tr></thead><tbody>@forelse($bumdes->assets as $asset)<tr><td>{{ $asset->nama }}</td><td>{{ $asset->jenis }}</td><td>{{ $asset->status_kepemilikan }}</td><td>{{ $asset->nilai_perkiraan !== null ? 'Rp '.Number::format($asset->nilai_perkiraan, locale: 'id') : 'Belum diisi' }}</td></tr>@empty<tr><td colspan="4">Belum ada data aset.</td></tr>@endforelse</tbody></table>
<section class="financial-summary"><h2>Ringkasan kinerja keuangan tahunan</h2><table><thead><tr><th>Tahun</th><th>Modal awal</th><th>Omzet</th><th>SHU</th><th>Kontribusi PADes</th></tr></thead><tbody>@forelse($bumdes->financialSummaries as $summary)<tr><td>{{ $summary->tahun }}</td>@foreach(['modal_awal', 'omzet', 'shu', 'kontribusi_pades'] as $field)<td>{{ $summary->{$field} !== null ? 'Rp '.Number::format($summary->{$field}, locale: 'id') : 'Belum diisi' }}</td>@endforeach</tr>@empty<tr><td colspan="5">Belum ada ringkasan keuangan.</td></tr>@endforelse</tbody></table></section>
<h2>Galeri</h2>@forelse($gallery as $image)<figure><img src="{{ $image['src'] }}" alt="{{ $image['title'] }}"><figcaption>{{ $image['title'] }}</figcaption></figure>@empty<p class="muted">Dokumentasi foto belum tersedia.</p>@endforelse
</body></html>
