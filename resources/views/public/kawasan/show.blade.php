@extends('layouts.public', ['title' => $record->nama])

@section('content')
    <div class="mx-auto max-w-[88rem] px-4 py-9 sm:px-7 sm:py-12 lg:px-10">
        <a href="{{ route('public.kawasan.index') }}" class="back-link">Kembali ke daftar kawasan</a>
        <header class="mt-8 grid gap-8 border-b border-stone-300 pb-10 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-end">
            <div><p class="text-xs font-bold text-emerald-800">{{ $record->categories->isNotEmpty() ? $record->categories->pluck('nama')->join(' / ') : 'Kawasan perdesaan' }}</p><h1 class="mt-3 max-w-5xl font-display text-4xl leading-tight text-stone-950 sm:text-6xl">{{ $record->nama }}</h1><p class="mt-5 max-w-3xl whitespace-pre-line text-base leading-7 text-stone-600">{{ $record->deskripsi ?: 'Deskripsi kawasan sedang dilengkapi oleh pengelola.' }}</p></div>
            <dl class="border-l-2 border-emerald-800 pl-5 text-sm"><dt class="text-stone-500">Status</dt><dd class="mt-1 font-semibold text-stone-950">Ditetapkan</dd><dt class="mt-4 text-stone-500">Tanggal penetapan</dt><dd class="mt-1 font-semibold text-stone-950">{{ $record->tanggal_penetapan?->translatedFormat('d F Y') ?? 'Belum dicantumkan' }}</dd></dl>
        </header>

        <section aria-labelledby="villages-heading" class="py-10">
            <h2 id="villages-heading" class="font-display text-3xl text-stone-950">Desa anggota</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($record->desas as $desa)
                    <div class="border border-stone-300 bg-white p-5"><p class="font-semibold text-stone-950">{{ $desa->nama }}</p><p class="mt-2 text-sm text-stone-500">Kecamatan {{ $desa->kecamatan->nama }}</p></div>
                @empty
                    <p class="border-l-2 border-emerald-800 pl-5 text-sm text-stone-600">Belum ada desa anggota yang dicantumkan.</p>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="potentials-heading" class="border-t border-stone-300 py-10">
            <h2 id="potentials-heading" class="font-display text-3xl text-stone-950">Potensi Published dalam kawasan</h2>
            @if ($potentials->isEmpty())
                <p class="mt-6 border-l-2 border-emerald-800 pl-5 text-sm leading-6 text-stone-600">Belum ada potensi Published dari desa anggota kawasan ini.</p>
            @else
                <div class="mt-7 grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($potentials as $potential)
                        <article class="min-w-0"><a href="{{ route('katalog-potensi.show', $potential) }}" class="group block focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-800">@if($potential->coverImage)<div class="overflow-hidden"><img data-public-image src="{{ route('katalog-potensi.media', [$potential, $potential->coverImage]) }}" alt="{{ $potential->coverImage->judul ?: 'Foto '.$potential->judul }}" width="720" height="540" loading="lazy" decoding="async" class="aspect-[4/3] w-full bg-stone-200 object-cover"><div data-public-image-fallback hidden class="flex aspect-[4/3] items-end bg-emerald-900 p-5"><span class="font-display text-2xl text-emerald-100">{{ $potential->category->nama }}</span></div></div>@else<div class="flex aspect-[4/3] items-end bg-emerald-900 p-5"><span class="font-display text-2xl text-emerald-100">{{ $potential->category->nama }}</span></div>@endif<p class="mt-4 text-xs font-bold text-emerald-800">{{ $potential->village->nama }} / {{ $potential->category->nama }}</p><h3 class="mt-2 font-display text-2xl leading-tight group-hover:text-emerald-800">{{ $potential->judul }}</h3></a></article>
                    @endforeach
                </div>
                @if ($potentials->hasPages())<div class="mt-9 border-t border-stone-300 pt-6">{{ $potentials->links() }}</div>@endif
            @endif
        </section>
    </div>
@endsection
