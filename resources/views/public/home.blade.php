@extends('layouts.public', [
    'title' => 'Beranda',
    'description' => 'Portal publik kawasan perdesaan, potensi desa, BUMDes, peta, kemitraan, dan proposal Kabupaten Madiun.',
])

@php($heroPotential = $potentials->first(fn ($potential) => $potential->coverImage !== null))

@section('content')
    <section class="bg-emerald-950 text-white">
        <div class="mx-auto grid max-w-[88rem] gap-8 px-4 py-12 sm:px-7 sm:py-16 lg:min-h-[39rem] lg:grid-cols-[minmax(0,1.04fr)_minmax(24rem,0.96fr)] lg:items-center lg:px-10 lg:py-20">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-300">Portal publik Kabupaten Madiun</p>
                <h1 class="mt-5 max-w-4xl font-display text-5xl leading-[1.04] text-balance sm:text-6xl lg:text-7xl">Desa tumbuh dari data yang terbuka.</h1>
                <p class="mt-6 max-w-2xl text-base leading-7 text-emerald-100/85 sm:text-lg">Jelajahi kawasan, potensi, BUMDes, dan proposal publik Kabupaten Madiun dalam satu ruang yang tepercaya.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('katalog-potensi.index') }}" class="inline-flex min-h-12 items-center justify-center bg-amber-300 px-5 text-sm font-bold text-emerald-950 transition hover:bg-amber-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white active:translate-y-px motion-reduce:transform-none">Jelajahi potensi</a>
                    <a href="{{ route('peta.index') }}" class="inline-flex min-h-12 items-center justify-center border border-white/25 px-5 text-sm font-bold text-white transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">Buka peta</a>
                </div>
            </div>

            <div class="min-w-0">
                @if ($heroPotential)
                    <a href="{{ route('katalog-potensi.show', $heroPotential) }}" class="group block border border-white/15 bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-300">
                        <img src="{{ route('katalog-potensi.media', [$heroPotential, $heroPotential->coverImage]) }}" alt="{{ $heroPotential->coverImage->judul ?: 'Foto '.$heroPotential->judul }}" width="960" height="720" fetchpriority="high" class="aspect-[4/3] w-full object-cover">
                        <div class="grid gap-2 p-5 sm:grid-cols-[1fr_auto] sm:items-end"><div><p class="text-xs font-semibold text-emerald-200">{{ $heroPotential->village->nama }}, {{ $heroPotential->village->kecamatan->nama }}</p><p class="mt-2 font-display text-2xl group-hover:text-amber-200">{{ $heroPotential->judul }}</p></div><span class="text-sm font-bold text-amber-300">Lihat detail</span></div>
                    </a>
                @else
                    <div class="border border-white/15 bg-emerald-900 p-6 sm:p-8">
                        <p class="font-display text-3xl text-emerald-50">Data publik yang terukur.</p>
                        <dl class="mt-8 grid grid-cols-2 gap-x-6 gap-y-8">
                            <div><dt class="text-xs leading-5 text-emerald-200">Kawasan ditetapkan</dt><dd class="mt-2 font-display text-4xl text-white">{{ Number::format($statistics['kawasans'], locale: 'id') }}</dd></div>
                            <div><dt class="text-xs leading-5 text-emerald-200">Potensi terbit</dt><dd class="mt-2 font-display text-4xl text-white">{{ Number::format($statistics['potentials'], locale: 'id') }}</dd></div>
                            <div><dt class="text-xs leading-5 text-emerald-200">Website BUMDes aktif</dt><dd class="mt-2 font-display text-4xl text-white">{{ Number::format($statistics['bumdesWebsites'], locale: 'id') }}</dd></div>
                            <div><dt class="text-xs leading-5 text-emerald-200">Proposal publik</dt><dd class="mt-2 font-display text-4xl text-white">{{ Number::format($statistics['proposals'], locale: 'id') }}</dd></div>
                        </dl>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section aria-labelledby="search-heading" class="border-b border-stone-300 bg-white">
        <div class="mx-auto max-w-[88rem] px-4 py-8 sm:px-7 lg:px-10">
            <h2 id="search-heading" class="font-display text-2xl text-stone-950">Apa yang ingin Anda temukan?</h2>
            <form method="GET" action="{{ route('katalog-potensi.index') }}" class="mt-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_16rem_auto]">
                <div><label for="home-search" class="form-label">Kata pencarian</label><input id="home-search" name="search" class="form-input" placeholder="Contoh: kopi, wisata, atau nama desa"></div>
                <div><label for="home-category" class="form-label">Kategori potensi</label><select id="home-category" name="kategori" class="form-input"><option value="">Semua kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->nama }}</option>@endforeach</select></div>
                <button class="btn-primary self-end">Cari potensi</button>
            </form>
        </div>
    </section>

    <section aria-labelledby="potential-heading" class="mx-auto max-w-[88rem] px-4 py-14 sm:px-7 sm:py-20 lg:px-10">
        <p class="eyebrow">Potensi terbaru</p>
        <h2 id="potential-heading" class="mt-2 max-w-3xl font-display text-3xl leading-tight sm:text-5xl">Peluang lokal yang siap dikenali lebih jauh.</h2>
        @if ($potentials->isEmpty())
            <div class="mt-8 border-l-2 border-emerald-800 bg-white p-6"><p class="font-semibold">Belum ada potensi Published.</p><p class="mt-2 text-sm leading-6 text-stone-600">Potensi akan tampil setelah melewati proses publikasi pengelola.</p></div>
        @else
            <div class="mt-9 grid gap-7 lg:grid-cols-[minmax(0,1.25fr)_minmax(20rem,0.75fr)]">
                @php($primaryPotential = $potentials->first())
                <article class="min-w-0 border-b border-stone-300 pb-7 lg:border-b-0 lg:border-r lg:pb-0 lg:pr-7">
                    <a href="{{ route('katalog-potensi.show', $primaryPotential) }}" class="group block focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-800">
                        @if ($primaryPotential->coverImage)<img src="{{ route('katalog-potensi.media', [$primaryPotential, $primaryPotential->coverImage]) }}" alt="{{ $primaryPotential->coverImage->judul ?: 'Foto '.$primaryPotential->judul }}" width="960" height="720" loading="lazy" decoding="async" class="aspect-[16/10] w-full bg-stone-200 object-cover">@else<div class="flex aspect-[16/10] items-end bg-emerald-900 p-7"><span class="font-display text-3xl text-emerald-100">{{ $primaryPotential->category->nama }}</span></div>@endif
                        <p class="mt-5 text-xs font-bold text-emerald-800">{{ $primaryPotential->category->nama }} / {{ $primaryPotential->village->nama }}</p>
                        <h3 class="mt-2 font-display text-3xl leading-tight group-hover:text-emerald-800 sm:text-4xl">{{ $primaryPotential->judul }}</h3>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-stone-600">{{ str($primaryPotential->deskripsi)->limit(150) }}</p>
                    </a>
                </article>
                <div class="space-y-7">
                    @foreach ($potentials->skip(1) as $potential)
                        <article class="border-b border-stone-300 pb-7 last:border-b-0 last:pb-0"><a href="{{ route('katalog-potensi.show', $potential) }}" class="group block focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-emerald-800"><p class="text-xs font-bold text-emerald-800">{{ $potential->category->nama }} / {{ $potential->village->nama }}</p><h3 class="mt-2 font-display text-2xl leading-tight group-hover:text-emerald-800">{{ $potential->judul }}</h3><p class="mt-2 text-sm leading-6 text-stone-600">{{ str($potential->deskripsi)->limit(92) }}</p></a></article>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('katalog-potensi.index') }}" class="btn-secondary mt-9">Lihat seluruh potensi</a>
        @endif
    </section>

    <section aria-labelledby="map-heading" class="border-y border-stone-300 bg-stone-50">
        <div class="mx-auto max-w-[88rem] px-4 py-14 sm:px-7 sm:py-20 lg:px-10">
            <h2 id="map-heading" class="font-display text-3xl text-stone-950 sm:text-5xl">Lihat wilayah, lalu temukan potensinya.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-6 text-stone-600 sm:text-base">Peta publik memuat kawasan ditetapkan, titik informasi aktif, BUMDes, dan potensi Published.</p>
            <div class="mt-8">@include('maps.partials.explorer', ['collection' => $mapCollection])</div>
            <a href="{{ route('peta.index') }}" class="btn-secondary mt-6">Buka halaman peta</a>
        </div>
    </section>

    <section aria-labelledby="kawasan-heading" class="mx-auto max-w-[88rem] px-4 py-14 sm:px-7 sm:py-20 lg:px-10">
        <h2 id="kawasan-heading" class="font-display text-3xl text-stone-950 sm:text-5xl">Kawasan yang bergerak bersama.</h2>
        <p class="mt-4 max-w-2xl text-sm leading-6 text-stone-600 sm:text-base">Kolaborasi antardesa yang telah ditetapkan dan tersedia untuk dijelajahi publik.</p>
        <div class="mt-8 grid gap-x-10 gap-y-7 md:grid-cols-2">
            @forelse ($kawasans as $kawasan)
                <article class="border-t-2 border-emerald-800 pt-5"><p class="text-xs font-semibold text-stone-500">{{ $kawasan->desas_count }} desa{{ $kawasan->categories->isNotEmpty() ? ' / '.$kawasan->categories->pluck('nama')->join(', ') : '' }}</p><h3 class="mt-2 font-display text-2xl text-stone-950">{{ $kawasan->nama }}</h3><p class="mt-3 text-sm leading-6 text-stone-600">{{ str($kawasan->deskripsi ?: 'Profil kawasan sedang dilengkapi pengelola.')->limit(120) }}</p><a href="{{ route('public.kawasan.show', $kawasan) }}" class="back-link mt-4">Lihat kawasan</a></article>
            @empty
                <div class="border-l-2 border-emerald-800 bg-white p-6 md:col-span-2"><p class="font-semibold">Belum ada kawasan berstatus Ditetapkan.</p><p class="mt-2 text-sm text-stone-600">Kawasan draft tetap tersimpan di ruang kerja dan tidak dibuka ke publik.</p></div>
            @endforelse
        </div>
        @if ($kawasans->isNotEmpty())<a href="{{ route('public.kawasan.index') }}" class="btn-secondary mt-9">Lihat semua kawasan</a>@endif
    </section>

    <section aria-labelledby="bumdes-heading" class="bg-emerald-950 text-white">
        <div class="mx-auto max-w-[88rem] px-4 py-14 sm:px-7 sm:py-20 lg:px-10">
            <h2 id="bumdes-heading" class="max-w-3xl font-display text-3xl sm:text-5xl">Usaha desa dengan etalase yang aktif.</h2>
            <div class="mt-9 grid gap-x-8 gap-y-7 sm:grid-cols-2">
                @forelse ($websites as $website)
                    <article class="border-t border-white/25 pt-5"><p class="text-xs text-emerald-200">{{ $website->bumdes->jenis->label() }}{{ $website->bumdes->desas->isNotEmpty() ? ' / '.$website->bumdes->desas->pluck('nama')->join(', ') : '' }}</p><h3 class="mt-2 font-display text-2xl">{{ $website->bumdes->nama }}</h3><p class="mt-3 text-sm leading-6 text-emerald-100/75">{{ str($website->bumdes->deskripsi ?: 'Profil usaha desa dan layanan yang dikelola masyarakat.')->limit(110) }}</p><a href="{{ route('public.bumdes.show', $website->subdomain) }}" class="mt-4 inline-flex min-h-10 items-center font-bold text-amber-300 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">Buka website</a></article>
                @empty
                    <p class="border-l-2 border-amber-300 pl-5 text-sm leading-6 text-emerald-100">Belum ada website BUMDes yang aktif untuk publik.</p>
                @endforelse
            </div>
            @if ($websites->isNotEmpty())<a href="{{ route('public.bumdes.index') }}" class="mt-9 inline-flex min-h-11 items-center border border-white/25 px-5 text-sm font-bold hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">Buka direktori BUMDes</a>@endif
        </div>
    </section>

    <section aria-labelledby="opportunity-heading" class="mx-auto max-w-[88rem] px-4 py-14 sm:px-7 sm:py-20 lg:px-10">
        <h2 id="opportunity-heading" class="font-display text-3xl text-stone-950 sm:text-5xl">Ruang kemitraan yang sedang dibutuhkan.</h2>
        <p class="mt-4 max-w-2xl text-sm leading-6 text-stone-600 sm:text-base">Peluang berikut berasal dari BUMDes dengan website aktif dan status kebutuhan kemitraan yang masih terbuka.</p>
        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(18rem,0.9fr)]">
            @forelse ($opportunities as $opportunity)
                <article @class(['border border-stone-300 bg-white p-6 sm:p-8', 'lg:row-span-2' => $loop->first])><p class="text-xs font-bold text-emerald-800">{{ $opportunity->bumdes->nama }}</p><h3 class="mt-3 font-display text-2xl text-stone-950 {{ $loop->first ? 'sm:text-4xl' : '' }}">{{ $opportunity->jenis_kemitraan }}</h3><p class="mt-3 text-sm leading-6 text-stone-600">{{ $opportunity->deskripsi ?: 'Rincian kebutuhan dapat dipelajari melalui website BUMDes terkait.' }}</p><p class="mt-5 text-sm font-semibold text-stone-900">{{ $opportunity->kebutuhan_investasi !== null ? 'Kebutuhan indikatif Rp '.number_format((float) $opportunity->kebutuhan_investasi, 0, ',', '.') : 'Nilai investasi terbuka untuk pembahasan' }}</p><a href="{{ route('public.bumdes.show', $opportunity->bumdes->website->subdomain) }}" class="back-link mt-5">Lihat profil pengelola</a></article>
            @empty
                <div class="border-l-2 border-emerald-800 bg-white p-6 lg:col-span-2"><p class="font-semibold">Belum ada kebutuhan kemitraan publik.</p><p class="mt-2 text-sm text-stone-600">Peluang akan tampil setelah website BUMDes aktif dan status kemitraan ditandai Dibutuhkan.</p></div>
            @endforelse
        </div>
    </section>

    <section aria-labelledby="proposal-heading" class="border-y border-stone-300 bg-white">
        <div class="mx-auto max-w-[88rem] px-4 py-14 sm:px-7 sm:py-20 lg:px-10">
            <p class="eyebrow">Proposal publik</p>
            <h2 id="proposal-heading" class="mt-2 font-display text-3xl text-stone-950 sm:text-5xl">Dokumen peluang yang telah diverifikasi.</h2>
            <div class="mt-8">
                @forelse ($proposals as $proposal)
                    <article class="grid gap-4 border-b border-stone-300 py-6 first:border-t sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center"><div><p class="text-xs font-semibold text-emerald-800">{{ $proposal->potential->category->nama }} / {{ $proposal->potential->village->nama }}</p><h3 class="mt-2 text-lg font-semibold text-stone-950">{{ $proposal->potential->judul }}</h3><p class="mt-1 text-sm text-stone-500">{{ $proposal->nomor_proposal ?: 'Nomor proposal belum dicantumkan' }}</p></div><a href="{{ route('public.proposal.download', $proposal) }}" class="btn-secondary">Unduh proposal</a></article>
                @empty
                    <div class="border-l-2 border-emerald-800 bg-stone-50 p-6"><p class="font-semibold">Belum ada proposal Published.</p><p class="mt-2 text-sm text-stone-600">Dokumen terverifikasi yang belum dipublikasikan tidak dapat diunduh dari portal.</p></div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
