@extends('layouts.public', [
    'title' => 'Beranda',
    'description' => 'Portal publik kawasan perdesaan, potensi desa, BUMDes, peta, kemitraan, dan proposal Kabupaten Madiun.',
])

@section('content')
    <section class="relative isolate min-h-[34rem] overflow-hidden bg-stone-800 sm:min-h-[39rem] lg:min-h-[42rem]">
        <img
            src="{{ asset('images/madiun2.jpg') }}"
            alt=""
            width="1200"
            height="630"
            fetchpriority="high"
            class="absolute inset-0 size-full scale-[1.01] object-cover object-[58%_center]"
            aria-hidden="true"
        >
        <div class="absolute inset-0 bg-stone-50/45 sm:bg-[linear-gradient(90deg,rgba(250,250,249,0.72)_0%,rgba(250,250,249,0.48)_34%,rgba(250,250,249,0.10)_58%,transparent_76%)]" aria-hidden="true"></div>
        <div class="absolute inset-y-0 left-0 w-full bg-white/[0.02] backdrop-blur-[0.25px] [mask-image:linear-gradient(to_right,black_0%,black_60%,transparent_100%)] sm:w-[64%]" aria-hidden="true"></div>

        <div class="relative mx-auto flex min-h-[34rem] max-w-[88rem] items-center px-4 py-12 sm:min-h-[39rem] sm:px-7 sm:py-16 lg:min-h-[42rem] lg:px-10">
            <div class="max-w-3xl min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-950">Portal publik Kabupaten Madiun</p>
                <h1 class="mt-5 max-w-3xl text-4xl font-bold leading-[1.06] tracking-[-0.04em] text-balance text-stone-950 sm:text-5xl lg:text-7xl">Desa tumbuh dari data yang terbuka.</h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-stone-700 sm:text-lg sm:leading-8">Jelajahi kawasan, potensi, BUMDes, dan proposal publik Kabupaten Madiun dalam satu ruang tepercaya.</p>
                <div class="mt-8 grid max-w-sm grid-cols-2 gap-3 sm:flex sm:max-w-none sm:flex-wrap">
                    <a href="{{ route('katalog-potensi.index') }}" class="inline-flex min-h-12 items-center justify-center bg-emerald-800 px-5 text-sm font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800 active:translate-y-px motion-reduce:transform-none">Jelajahi potensi</a>
                    <a href="{{ route('peta.index') }}" class="inline-flex min-h-12 items-center justify-center border border-stone-300 bg-stone-50/85 px-5 text-sm font-bold text-stone-900 backdrop-blur-sm transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800 active:translate-y-px motion-reduce:transform-none">Buka peta</a>
                </div>
            </div>
        </div>
    </section>

    <section aria-labelledby="search-heading" class="relative z-10 bg-stone-100">
        <div class="mx-auto -mt-8 max-w-[88rem] px-4 sm:px-7 lg:px-10">
            <div class="border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.10)] sm:p-7">
            <h2 id="search-heading" class="font-display text-2xl text-stone-950 sm:text-3xl">Apa yang ingin Anda temukan?</h2>
            <form method="GET" action="{{ route('katalog-potensi.index') }}" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_16rem_auto]">
                <div><label for="home-search" class="form-label">Kata pencarian</label><input id="home-search" name="search" class="form-input" placeholder="Contoh: kopi, wisata, atau nama desa"></div>
                <div><label for="home-category" class="form-label">Kategori potensi</label><select id="home-category" name="kategori" class="form-input"><option value="">Semua kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->nama }}</option>@endforeach</select></div>
                <button class="btn-primary w-full self-end sm:col-span-2 lg:col-span-1 lg:w-auto">Cari potensi</button>
            </form>
            </div>
        </div>
    </section>

    <section aria-labelledby="potential-heading" class="mx-auto max-w-[88rem] px-4 py-14 sm:px-7 sm:py-20 lg:px-10 lg:py-24">
        <p class="eyebrow">Potensi terbaru</p>
        <h2 id="potential-heading" class="mt-2 max-w-3xl font-display text-3xl leading-tight sm:text-5xl">Peluang lokal yang siap dikenali lebih jauh.</h2>
        @if ($potentials->isEmpty())
            <div class="mt-8 border-l-2 border-emerald-800 bg-white p-6"><p class="font-semibold">Belum ada potensi Published.</p><p class="mt-2 text-sm leading-6 text-stone-600">Potensi akan tampil setelah melewati proses publikasi pengelola.</p></div>
        @else
            <div class="mt-9 grid gap-7 lg:grid-cols-[minmax(0,1.25fr)_minmax(20rem,0.75fr)]">
                @php($primaryPotential = $potentials->first())
                <article class="min-w-0 border-b border-stone-300 pb-7 lg:border-b-0 lg:border-r lg:pb-0 lg:pr-7">
                    <a href="{{ route('katalog-potensi.show', $primaryPotential) }}" class="group block focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-800">
                        <div class="overflow-hidden">@if ($primaryPotential->coverImage)<img data-public-image src="{{ route('katalog-potensi.media', [$primaryPotential, $primaryPotential->coverImage]) }}" alt="{{ $primaryPotential->coverImage->judul ?: 'Foto '.$primaryPotential->judul }}" width="960" height="720" loading="lazy" decoding="async" class="aspect-[16/10] w-full bg-stone-200 object-cover transition-transform duration-500 group-hover:scale-[1.025] motion-reduce:transform-none"><div data-public-image-fallback hidden class="flex aspect-[16/10] items-end bg-emerald-900 p-7"><span class="font-display text-3xl text-emerald-100">{{ $primaryPotential->category->nama }}</span></div>@else<div class="flex aspect-[16/10] items-end bg-emerald-900 p-7"><span class="font-display text-3xl text-emerald-100">{{ $primaryPotential->category->nama }}</span></div>@endif</div>
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

    <section aria-labelledby="map-heading" class="border-y border-stone-300 bg-white">
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
                <article class="border border-stone-200 border-t-2 border-t-emerald-800 bg-white p-5 transition duration-200 hover:-translate-y-0.5 hover:border-stone-300 hover:shadow-[0_14px_35px_rgba(28,25,23,0.08)] motion-reduce:transform-none sm:p-6"><p class="text-xs font-semibold text-stone-500">{{ $kawasan->desas_count }} desa{{ $kawasan->categories->isNotEmpty() ? ' / '.$kawasan->categories->pluck('nama')->join(', ') : '' }}</p><h3 class="mt-2 font-display text-2xl text-stone-950">{{ $kawasan->nama }}</h3><p class="mt-3 text-sm leading-6 text-stone-600">{{ str($kawasan->deskripsi ?: 'Profil kawasan sedang dilengkapi pengelola.')->limit(120) }}</p><a href="{{ route('public.kawasan.show', $kawasan) }}" class="back-link mt-4">Lihat kawasan</a></article>
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
                <article @class(['border border-stone-300 bg-white p-6 transition duration-200 hover:border-emerald-700 hover:shadow-[0_14px_35px_rgba(28,25,23,0.07)] sm:p-8', 'lg:row-span-2' => $loop->first])><p class="text-xs font-bold text-emerald-800">{{ $opportunity->bumdes->nama }}</p><h3 class="mt-3 font-display text-2xl text-stone-950 {{ $loop->first ? 'sm:text-4xl' : '' }}">{{ $opportunity->jenis_kemitraan }}</h3><p class="mt-3 text-sm leading-6 text-stone-600">{{ $opportunity->deskripsi ?: 'Rincian kebutuhan dapat dipelajari melalui website BUMDes terkait.' }}</p><p class="mt-5 text-sm font-semibold text-stone-900">{{ $opportunity->kebutuhan_investasi !== null ? 'Kebutuhan indikatif Rp '.number_format((float) $opportunity->kebutuhan_investasi, 0, ',', '.') : 'Nilai investasi terbuka untuk pembahasan' }}</p><a href="{{ route('public.bumdes.show', $opportunity->bumdes->website->subdomain) }}" class="back-link mt-5">Lihat profil pengelola</a></article>
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
                    <article class="grid gap-4 border-b border-stone-300 py-6 first:border-t sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center"><div><p class="text-xs font-semibold text-emerald-800">{{ $proposal->potential->category->nama }} / {{ $proposal->potential->village->nama }}</p><h3 class="mt-2 text-lg font-semibold text-stone-950">{{ $proposal->potential->judul }}</h3><p class="mt-1 text-sm text-stone-500">{{ $proposal->nomor_proposal ?: 'Nomor proposal belum dicantumkan' }}</p></div><a href="{{ route('public.proposal.download', $proposal) }}" class="btn-secondary w-full sm:w-auto">Unduh proposal</a></article>
                @empty
                    <div class="border-l-2 border-emerald-800 bg-stone-50 p-6"><p class="font-semibold">Belum ada proposal Published.</p><p class="mt-2 text-sm text-stone-600">Dokumen terverifikasi yang belum dipublikasikan tidak dapat diunduh dari portal.</p></div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
