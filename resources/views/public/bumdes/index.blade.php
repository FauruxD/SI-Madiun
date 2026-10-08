@extends('layouts.public', ['title' => 'Direktori BUMDes'])

@section('content')
    <div class="mx-auto max-w-[88rem] px-4 py-10 sm:px-7 sm:py-14 lg:px-10">
        <header class="max-w-4xl">
            <p class="eyebrow">Etalase usaha desa</p>
            <h1 class="mt-3 font-display text-4xl leading-tight text-stone-950 sm:text-6xl">BUMDes yang dapat dikunjungi publik.</h1>
            <p class="mt-5 max-w-2xl text-base leading-7 text-stone-600">Direktori hanya memuat BUMDes dan BUMDesma dengan website yang telah diaktifkan pengelola.</p>
        </header>

        <form method="GET" class="mt-9 grid gap-3 border-y border-stone-300 py-5 sm:grid-cols-[minmax(0,1fr)_auto]">
            <div><label for="search" class="form-label">Cari BUMDes atau desa</label><input id="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Masukkan nama BUMDes atau desa"></div>
            <button class="btn-primary self-end">Cari BUMDes</button>
        </form>

        <section aria-label="Daftar BUMDes" class="py-10">
            @if ($websites->isEmpty())
                <div class="border-l-2 border-emerald-800 bg-white p-6"><p class="font-semibold">Belum ada BUMDes yang cocok.</p><p class="mt-2 text-sm leading-6 text-stone-600">Ubah kata pencarian atau kembali saat website BUMDes telah aktif.</p></div>
            @else
                <div class="grid gap-x-9 gap-y-9 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($websites as $website)
                        <article class="border-t-2 border-emerald-800 pt-5"><p class="text-xs font-semibold text-stone-500">{{ $website->bumdes->jenis->label() }}{{ $website->bumdes->desas->isNotEmpty() ? ' / '.$website->bumdes->desas->pluck('nama')->join(', ') : '' }}</p><h2 class="mt-2 font-display text-2xl leading-tight text-stone-950">{{ $website->bumdes->nama }}</h2><p class="mt-3 text-sm leading-6 text-stone-600">{{ str($website->bumdes->deskripsi ?: 'Profil usaha desa dan produk yang dikelola masyarakat.')->limit(130) }}</p><a href="{{ route('public.bumdes.show', $website->subdomain) }}" class="back-link mt-5">Buka website</a></article>
                    @endforeach
                </div>
            @endif
            @if ($websites->hasPages())<div class="mt-10 border-t border-stone-300 pt-6">{{ $websites->links() }}</div>@endif
        </section>
    </div>
@endsection
