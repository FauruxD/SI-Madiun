@extends('layouts.public', ['title' => 'Tentang Sistem'])

@section('content')
    <div class="mx-auto max-w-[88rem] px-4 py-10 sm:px-7 sm:py-14 lg:px-10">
        <header class="max-w-4xl"><p class="eyebrow">Tentang sistem</p><h1 class="mt-3 font-display text-4xl leading-tight text-stone-950 sm:text-6xl">Satu pintu informasi kawasan perdesaan Madiun.</h1><p class="mt-5 max-w-3xl text-base leading-7 text-stone-600">Portal ini membantu masyarakat, mitra, dan investor menemukan data kawasan, potensi desa, BUMDes, peta, dan proposal yang telah dipublikasikan.</p></header>

        <div class="mt-12 grid gap-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(20rem,0.85fr)]">
            <section aria-labelledby="purpose-heading"><h2 id="purpose-heading" class="font-display text-3xl text-stone-950">Tujuan layanan</h2><div class="mt-6 space-y-6 text-sm leading-7 text-stone-700"><p>Menyediakan informasi kawasan perdesaan yang konsisten, mudah dijelajahi, dan dapat dipertanggungjawabkan oleh pengelola resmi.</p><p>Mendekatkan potensi desa dan peluang kemitraan kepada pihak yang ingin mengenal atau bekerja sama dengan pelaku ekonomi lokal.</p><p>Mendukung proses kerja DPMD, Admin Kawasan, dan Admin Desa tanpa mencampurkan data internal dengan informasi yang memang layak dibuka ke publik.</p></div></section>
            <aside class="border-l-2 border-emerald-800 bg-white p-6 sm:p-8"><h2 class="font-display text-2xl text-stone-950">Batas informasi publik</h2><ul class="mt-5 space-y-4 text-sm leading-6 text-stone-600"><li>Potensi hanya tampil saat berstatus Published.</li><li>Kawasan hanya tampil saat berstatus Ditetapkan.</li><li>Direktori BUMDes hanya memuat website aktif.</li><li>Dokumen privat, data keuangan internal, dan draft tidak ditampilkan.</li><li>Company Profile PDF belum tersedia untuk publik sampai aturan publikasinya ditetapkan.</li></ul></aside>
        </div>

        <section aria-labelledby="explore-heading" class="mt-14 border-t border-stone-300 pt-10"><h2 id="explore-heading" class="font-display text-3xl text-stone-950">Mulai menjelajah</h2><div class="mt-6 flex flex-wrap gap-3"><a href="{{ route('public.kawasan.index') }}" class="btn-primary">Lihat kawasan</a><a href="{{ route('katalog-potensi.index') }}" class="btn-secondary">Cari potensi</a><a href="{{ route('peta.index') }}" class="btn-secondary">Buka peta</a></div></section>
    </div>
@endsection
