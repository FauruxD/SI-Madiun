@extends('layouts.public', ['title' => 'Katalog Potensi Desa'])

@section('content')
    <section class="relative overflow-hidden bg-emerald-950 text-white">
        <div class="absolute inset-0 opacity-40" aria-hidden="true"><div class="absolute -right-32 -top-32 size-[30rem] rounded-full border border-emerald-300/25"></div><div class="absolute -right-16 -top-12 size-[30rem] rounded-full border border-amber-200/15"></div></div>
        <div class="relative mx-auto max-w-[88rem] px-4 py-16 sm:px-7 sm:py-20 lg:px-10 lg:py-24">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-amber-300">Katalog terbuka Kabupaten Madiun</p>
            <h1 class="mt-5 max-w-4xl font-display text-4xl leading-[1.08] text-balance sm:text-6xl lg:text-7xl">Potensi lokal, peluang yang lebih dekat.</h1>
            <p class="mt-6 max-w-2xl text-base leading-7 text-emerald-100/80 sm:text-lg">Temukan komoditas, destinasi, dan usaha unggulan desa yang siap dikenal mitra serta investor.</p>
        </div>
    </section>

    <div class="mx-auto max-w-[88rem] px-4 sm:px-7 lg:px-10">
        <form method="GET" class="relative -mt-7 grid gap-3 border border-stone-200 bg-white p-4 shadow-[0_18px_45px_rgba(28,25,23,0.08)] md:grid-cols-[minmax(0,1fr)_16rem_auto] sm:p-5">
            <div><label for="search" class="sr-only">Cari judul atau desa</label><input id="search" name="search" value="{{ request('search') }}" class="form-input mt-0" placeholder="Cari judul, deskripsi, atau nama desa…"></div>
            <div>
                <label for="kategori" class="sr-only">Filter kategori</label>
                <select id="kategori" name="kategori" class="form-input mt-0">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)<option value="{{ $category->id }}" @selected((int) request('kategori') === $category->id)>{{ $category->nama }}</option>@endforeach
                </select>
            </div>
            <button class="btn-primary">Temukan potensi</button>
        </form>

        <section class="py-12 sm:py-16">
            <div class="flex flex-col gap-3 border-b border-stone-300 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="eyebrow">Potensi Published</p><h2 class="section-title">Pilihan dari desa-desa Madiun</h2></div>
                <p class="text-sm text-stone-500">{{ $potentials->total() }} potensi ditemukan</p>
            </div>

            @if ($potentials->isEmpty())
                <div class="border-b border-stone-300 py-20 text-center"><p class="font-display text-3xl text-stone-800">Belum ada hasil yang cocok.</p><p class="mt-3 text-sm text-stone-500">Ubah kata pencarian atau pilih kategori lain.</p></div>
            @else
                <div class="grid gap-x-6 gap-y-10 py-8 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($potentials as $potential)
                        <article class="group min-w-0">
                            <a href="{{ route('katalog-potensi.show', $potential) }}" class="block focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-800">
                                <div class="relative aspect-[4/3] overflow-hidden bg-stone-200">
                                    @if ($potential->coverImage)
                                        <img src="{{ route('katalog-potensi.media', [$potential, $potential->coverImage]) }}" alt="{{ $potential->coverImage->judul ?: 'Foto '.$potential->judul }}" class="size-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                    @else
                                        <div class="grid size-full place-items-center bg-emerald-900 px-8 text-center"><span class="font-display text-3xl text-emerald-100/70">{{ $potential->category->nama }}</span></div>
                                    @endif
                                    <span class="absolute left-4 top-4 bg-amber-300 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-950">{{ $potential->category->nama }}</span>
                                </div>
                                <div class="pt-5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.13em] text-emerald-800">{{ $potential->village->nama }} · {{ $potential->village->kecamatan->nama }}</p>
                                    <h3 class="mt-3 font-display text-2xl leading-tight text-stone-950 group-hover:text-emerald-800 sm:text-3xl">{{ $potential->judul }}</h3>
                                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ str($potential->deskripsi)->limit(135) }}</p>
                                    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-xs text-stone-500"><span>{{ $potential->bumdes?->nama ?? 'Dikelola desa' }}</span>@if ($potential->village->kawasans->isNotEmpty())<span>{{ $potential->village->kawasans->pluck('nama')->join(', ') }}</span>@endif</div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif

            @if ($potentials->hasPages())<div class="border-t border-stone-300 pt-6">{{ $potentials->links() }}</div>@endif
        </section>
    </div>
@endsection
