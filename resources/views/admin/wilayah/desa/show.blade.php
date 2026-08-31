@extends('layouts.app', ['title' => $desa->nama])

@section('content')
    <div class="max-w-4xl">
        <a href="{{ route('dashboard') }}" class="back-link">← Kembali ke dashboard</a>
        <div class="mt-7 border border-stone-200 bg-white p-7 sm:p-10">
            <p class="eyebrow">Desa dalam cakupan Anda</p>
            <h1 class="mt-3 font-display text-4xl sm:text-5xl">{{ $desa->nama }}</h1>
            <p class="mt-3 font-mono text-sm text-emerald-800">{{ $desa->kode }}</p>
            <dl class="mt-9 grid gap-px border border-stone-200 bg-stone-200 sm:grid-cols-2">
                <div class="bg-stone-50 p-5"><dt class="text-xs font-bold uppercase tracking-[0.12em] text-stone-500">Kecamatan</dt><dd class="mt-2 font-semibold">{{ $desa->kecamatan->nama }}</dd></div>
                <div class="bg-stone-50 p-5"><dt class="text-xs font-bold uppercase tracking-[0.12em] text-stone-500">Kabupaten</dt><dd class="mt-2 font-semibold">{{ $desa->kecamatan->kabupaten->nama }}</dd></div>
            </dl>
            <div class="mt-8 border-l-2 border-amber-400 bg-amber-50 p-5 text-sm leading-6 text-amber-950">Data profil, produk unggulan, dan peta desa akan tersedia pada phase implementasi berikutnya. Pembatasan akses desa ini sudah aktif di sisi server.</div>
        </div>
    </div>
@endsection
