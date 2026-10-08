<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 34px; }
        body { color: #292524; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.55; }
        .header { border-bottom: 2px solid #064e3b; padding-bottom: 12px; margin-bottom: 18px; }
        .kicker { color: #047857; font-size: 9px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        h1 { color: #064e3b; font-family: DejaVu Serif, serif; font-size: 21px; margin: 5px 0 0; }
        h2 { color: #064e3b; font-size: 13px; margin: 18px 0 7px; }
        .muted { color: #78716c; }
        table { border-collapse: collapse; width: 100%; }
        td { border-bottom: 1px solid #e7e5e4; padding: 5px 4px; vertical-align: top; }
        td:first-child { color: #78716c; width: 28%; }
        .copy { white-space: pre-line; }
        .gallery { margin-top: 8px; }
        .photo { display: inline-block; margin: 0 8px 8px 0; vertical-align: top; width: 31%; }
        .photo img { height: 115px; object-fit: cover; width: 100%; }
        .photo p { margin: 2px 0; color: #78716c; }
        .footer { border-top: 1px solid #d6d3d1; color: #78716c; font-size: 8px; margin-top: 24px; padding-top: 7px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="kicker">Sistem Informasi Kawasan Perdesaan</div>
        <h1>Proposal Potensi Desa</h1>
        <div class="muted">Kabupaten Madiun · proposal potensi yang telah terverifikasi</div>
    </header>

    <table>
        <tr><td>Nomor proposal</td><td>{{ $proposal->nomor_proposal ?: 'Belum ditetapkan' }}</td></tr>
        <tr><td>Status</td><td>{{ $status->label() }}</td></tr>
        <tr><td>Tanggal pengajuan</td><td>{{ $proposal->submitted_at?->translatedFormat('d F Y') ?: '—' }}</td></tr>
        <tr><td>Desa</td><td>{{ $potential->village->nama }}, Kecamatan {{ $potential->village->kecamatan->nama }}, {{ $potential->village->kecamatan->kabupaten->nama }}</td></tr>
        <tr><td>Kawasan</td><td>{{ $potential->village->kawasans->pluck('nama')->join(', ') ?: 'Belum terhubung' }}</td></tr>
        <tr><td>Kategori</td><td>{{ $potential->category->nama }}</td></tr>
        <tr><td>BUMDes</td><td>{{ $potential->bumdes?->nama ?: 'Tidak ditautkan' }}</td></tr>
        <tr><td>Kebutuhan investasi</td><td>{{ $potential->kebutuhan_investasi !== null ? 'Rp '.number_format((float) $potential->kebutuhan_investasi, 0, ',', '.') : 'Belum ditentukan' }}</td></tr>
    </table>

    <h2>{{ $potential->judul }}</h2>
    <div class="copy">{{ $potential->deskripsi }}</div>

    @if (filled($potential->data_pendukung))
        <h2>Data pendukung</h2>
        <div class="copy">{{ $potential->data_pendukung }}</div>
    @endif

    <h2>Galeri</h2>
    @if ($images->isNotEmpty())
        <div class="gallery">
            @foreach ($images as $image)
                <div class="photo"><img src="{{ $image['src'] }}" alt=""><p>{{ $image['title'] }}</p></div>
            @endforeach
        </div>
    @else
        <p class="muted">Foto pendukung belum tersedia atau tidak dapat dibaca.</p>
    @endif

    <div class="footer">Dokumen ini dibuat dari data yang telah terverifikasi pada Sistem Informasi Kawasan Perdesaan. Format kop, nomor surat, tanda tangan, dan QR verifikasi mengikuti kebijakan resmi yang akan ditetapkan.</div>
</body>
</html>
