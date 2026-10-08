@extends('layouts.app', ['title' => 'Website · '.$bumdes->nama])

@section('content')
    <a href="{{ route('bumdes.show', $bumdes) }}" class="back-link">← Profil BUMDes</a>
    <div class="mt-6 flex flex-col gap-5 border-b border-stone-300 pb-7 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="eyebrow">Publikasi digital</p><h1 class="page-title">Website BUMDes</h1><p class="page-lead">{{ $bumdes->nama }}. Website membaca profil, organisasi, produk aktif, dan galeri dari data yang sudah dikelola.</p></div>
        <span class="{{ $website->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $website->is_active ? 'Website aktif' : 'Website nonaktif' }}</span>
    </div>
    <div class="mt-8 grid min-w-0 gap-7 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,0.6fr)]">
        <form action="{{ route('bumdes.website.update', $bumdes) }}" method="POST" class="min-w-0 border border-stone-200 bg-white p-5 sm:p-7">
            @csrf @method('PUT')
            <h2 class="section-title">Alamat dan tampilan publik</h2>
            <div class="mt-6 grid gap-6">
                <div><label for="subdomain" class="form-label">Subdomain <span aria-hidden="true">*</span></label><input id="subdomain" name="subdomain" required maxlength="63" autocomplete="off" class="form-input" value="{{ old('subdomain', $website->subdomain) }}" aria-describedby="subdomain-help"><p id="subdomain-help" class="mt-2 break-all text-xs leading-5 text-stone-600">Alamat bawaan: {{ $website->defaultHost() }}. Gunakan huruf kecil, angka, dan tanda hubung.</p><x-input-error name="subdomain" /></div>
                <div><label for="custom_domain" class="form-label">Custom domain <span class="font-normal text-stone-500">(opsional)</span></label><input id="custom_domain" name="custom_domain" maxlength="253" autocomplete="off" class="form-input" placeholder="namabumdes.id" value="{{ old('custom_domain', $website->custom_domain) }}" aria-describedby="custom-domain-help"><p id="custom-domain-help" class="mt-2 text-xs leading-5 text-stone-600">Hostname saja, tanpa https:// atau path. Pendaftaran domain, verifikasi kepemilikan, DNS, dan TLS dilakukan pengelola hosting di luar aplikasi.</p><x-input-error name="custom_domain" /></div>
                <div><label for="is_active" class="form-label">Tampilan website <span aria-hidden="true">*</span></label><select id="is_active" name="is_active" required class="form-input" aria-describedby="visibility-help"><option value="0" @selected((string) old('is_active', (int) $website->is_active) === '0')>Nonaktif · tidak dapat diakses publik</option><option value="1" @selected((string) old('is_active', (int) $website->is_active) === '1')>Aktif · dapat diakses publik</option></select><p id="visibility-help" class="mt-2 text-xs leading-5 text-stone-600">Aktivasi menampilkan profil, nama/jabatan pengurus aktif, produk dari unit aktif, dan galeri. Kontak pribadi pengurus, legalitas privat, aset, dan keuangan tidak ditampilkan.</p><x-input-error name="is_active" /></div>
                <div class="flex flex-wrap gap-3"><button class="btn-primary">Simpan pengaturan</button>@if($website->is_active)<a href="{{ route('public.bumdes.show', $website->subdomain) }}" class="btn-secondary">Buka website</a>@endif</div>
            </div>
        </form>
        <aside class="min-w-0 space-y-6">
            <section class="border border-stone-200 bg-stone-50 p-5 sm:p-6"><h2 class="section-title">Kesiapan domain</h2><dl class="mt-5 space-y-4 text-sm"><div><dt class="text-stone-500">Subdomain bawaan</dt><dd class="mt-1 break-all font-semibold">{{ $website->defaultHost() }}</dd></div><div><dt class="text-stone-500">Custom domain</dt><dd class="mt-1 break-all font-semibold">{{ $website->custom_domain ?: 'Belum diatur' }}</dd><dd class="mt-2 text-xs leading-5 text-stone-600">{{ $website->custom_domain ? ($website->customDomainIsReady() ? 'Terverifikasi pada konfigurasi hosting.' : 'Menunggu verifikasi pengelola hosting. Belum dilayani aplikasi.') : 'Website tetap memiliki alamat subdomain bawaan.' }}</dd></div></dl><p class="mt-5 border-t border-stone-200 pt-4 text-xs leading-5 text-stone-600">DNS wildcard/base domain produksi belum ditetapkan. Tombol Buka website menggunakan alamat aplikasi agar pemeriksaan lokal tidak bergantung DNS.</p></section>
            <section class="border border-amber-200 bg-amber-50 p-5 sm:p-6"><h2 class="text-sm font-semibold text-amber-950">Company Profile PDF</h2><p class="mt-3 text-sm leading-6 text-amber-900">Unduhan admin berisi profil, organisasi, produk, aset, dan ringkasan keuangan. Website aktif tidak otomatis mempublikasikan dokumen ini.</p><a href="{{ route('bumdes.company-profile.download', $bumdes) }}" class="btn-secondary mt-5">Unduh Company Profile</a><p class="mt-3 text-xs leading-5 text-amber-900">Download publik menunggu aturan status published dan publikasi data keuangan sesuai SRS/PRD.</p></section>
        </aside>
    </div>
@endsection
