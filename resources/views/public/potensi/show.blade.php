@extends('layouts.public', ['title' => $potential->judul])

@php
    $images = $potential->media->filter(fn ($media) => $media->tipe_media === \App\Enums\TipeMediaPotensi::Image);
    $videos = $potential->media->filter(fn ($media) => $media->tipe_media === \App\Enums\TipeMediaPotensi::Video);
@endphp

@section('content')
    <div class="mx-auto max-w-[88rem] px-4 py-8 sm:px-7 sm:py-12 lg:px-10">
        <a href="{{ route('katalog-potensi.index', request()->only(['search', 'kategori'])) }}" class="back-link">← Kembali ke katalog</a>

        <header class="mt-8 grid gap-8 border-b border-stone-300 pb-10 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-end">
            <div><p class="eyebrow">{{ $potential->category->nama }} · {{ $potential->village->nama }}</p><h1 class="mt-4 max-w-5xl font-display text-4xl leading-[1.08] text-balance sm:text-6xl">{{ $potential->judul }}</h1><p class="mt-5 max-w-3xl text-base leading-7 text-stone-600">{{ str($potential->deskripsi)->limit(240) }}</p></div>
            <dl class="border-l-2 border-amber-300 pl-5 text-sm"><dt class="text-stone-500">Lokasi</dt><dd class="mt-1 font-semibold text-stone-950">Desa {{ $potential->village->nama }}</dd><dd class="mt-1 text-stone-600">Kecamatan {{ $potential->village->kecamatan->nama }}, {{ $potential->village->kecamatan->kabupaten->nama }}</dd></dl>
        </header>

        @if ($potential->eproposal?->status === \App\Enums\EProposalStatus::Published)
            <div class="mt-6">
                <a href="{{ route('public.proposal.download', $potential->eproposal) }}" class="btn-primary">Unduh Proposal</a>
            </div>
        @endif

        @if ($images->isNotEmpty())
            <section aria-labelledby="galeri-title" class="py-10">
                <div class="flex items-end justify-between gap-4"><div><p class="eyebrow">Dokumentasi</p><h2 id="galeri-title" class="section-title">Galeri potensi</h2></div><p class="text-sm text-stone-500">{{ $images->count() }} foto</p></div>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-12">
                    @foreach ($images as $image)
                        <figure @class(['overflow-hidden bg-stone-200', 'lg:col-span-8' => $loop->first, 'lg:col-span-4' => ! $loop->first])>
                            <img data-public-image src="{{ route('katalog-potensi.media', [$potential, $image]) }}" alt="{{ $image->judul ?: 'Foto '.$potential->judul }}" @class(['w-full object-cover', 'aspect-[16/10]' => $loop->first, 'aspect-[4/3]' => ! $loop->first])>
                            <div data-public-image-fallback hidden @class(['grid w-full place-items-center bg-emerald-900 p-6 text-center', 'aspect-[16/10]' => $loop->first, 'aspect-[4/3]' => ! $loop->first])><span class="font-display text-2xl text-emerald-100">{{ $potential->category->nama }}</span></div>
                            @if ($image->judul)<figcaption class="bg-white px-4 py-3 text-xs text-stone-600">{{ $image->judul }}</figcaption>@endif
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid gap-8 border-t border-stone-300 py-10 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <article>
                <p class="eyebrow">Tentang potensi</p>
                <div class="mt-5 whitespace-pre-line text-base leading-8 text-stone-700">{{ $potential->deskripsi }}</div>

                @if ($potential->data_pendukung)
                    <section class="mt-10 border-l-2 border-emerald-800 bg-white p-6 sm:p-8"><p class="eyebrow">Data pendukung investasi</p><div class="mt-4 whitespace-pre-line text-sm leading-7 text-stone-700">{{ $potential->data_pendukung }}</div></section>
                @endif

                @if ($videos->isNotEmpty())
                    <section class="mt-10"><p class="eyebrow">Video pendukung</p><div class="mt-4 grid gap-3 sm:grid-cols-2">@foreach ($videos as $video)<a href="{{ $video->media_url }}" target="_blank" rel="noopener noreferrer" class="group border border-stone-300 bg-white p-5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800"><span class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-800">Buka video ↗</span><span class="mt-2 block font-semibold text-stone-950 group-hover:text-emerald-800">{{ $video->judul ?: 'Video '.$potential->judul }}</span></a>@endforeach</div></section>
                @endif
            </article>

            <aside class="space-y-4">
                <section class="bg-emerald-950 p-6 text-white"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-300">Kebutuhan investasi</p><p class="mt-4 font-display text-3xl">{{ $potential->kebutuhan_investasi !== null ? 'Rp '.number_format((float) $potential->kebutuhan_investasi, 0, ',', '.') : 'Terbuka untuk diskusi' }}</p></section>
                <section class="border border-stone-300 bg-white p-6"><p class="eyebrow">Pengelola terkait</p><p class="mt-4 font-semibold text-stone-950">{{ $potential->bumdes?->nama ?? 'Pemerintah Desa '.$potential->village->nama }}</p>@if($potential->bumdes?->website?->is_active)<a href="{{ route('public.bumdes.show', $potential->bumdes->website->subdomain) }}" class="btn-secondary mt-4">Website BUMDes</a>@else<p class="mt-2 text-sm leading-6 text-stone-500">{{ $potential->bumdes ? 'Website BUMDes belum aktif.' : 'Belum ditautkan ke BUMDes/BUMDesma.' }}</p>@endif</section>
                @if ($potential->village->kawasans->isNotEmpty())<section class="border border-stone-300 bg-white p-6"><p class="eyebrow">Kawasan perdesaan</p><p class="mt-4 text-sm font-semibold leading-6 text-stone-900">{{ $potential->village->kawasans->pluck('nama')->join(', ') }}</p></section>@endif
            </aside>
        </div>
    </div>
@endsection
