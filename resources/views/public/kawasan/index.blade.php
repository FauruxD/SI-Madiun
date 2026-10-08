@extends('layouts.public', ['title' => 'Kawasan Perdesaan'])

@section('content')
    <div class="mx-auto max-w-[88rem] px-4 py-10 sm:px-7 sm:py-14 lg:px-10">
        <header class="max-w-4xl">
            <p class="eyebrow">Kawasan ditetapkan</p>
            <h1 class="mt-3 font-display text-4xl leading-tight text-stone-950 sm:text-6xl">Kolaborasi desa dalam satu kawasan.</h1>
            <p class="mt-5 max-w-2xl text-base leading-7 text-stone-600">Jelajahi kawasan perdesaan yang telah ditetapkan, desa anggotanya, dan potensi Published yang terkait.</p>
        </header>

        <form method="GET" class="mt-9 grid gap-3 border-y border-stone-300 py-5 sm:grid-cols-[minmax(0,1fr)_auto]">
            <div><label for="search" class="form-label">Cari nama kawasan</label><input id="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Contoh: Wilis atau agropolitan"></div>
            <button class="btn-primary self-end">Cari kawasan</button>
        </form>

        <section aria-label="Daftar kawasan" class="py-10">
            @if ($kawasans->isEmpty())
                <div class="border-l-2 border-emerald-800 bg-white p-6"><p class="font-semibold">Belum ada kawasan yang cocok.</p><p class="mt-2 text-sm leading-6 text-stone-600">Ubah kata pencarian atau kembali saat data kawasan telah ditetapkan.</p></div>
            @else
                <div class="grid gap-x-9 gap-y-10 md:grid-cols-2">
                    @foreach ($kawasans as $kawasan)
                        <article class="border-t-2 border-emerald-800 pt-5"><p class="text-xs font-semibold text-stone-500">{{ $kawasan->desas_count }} desa{{ $kawasan->tanggal_penetapan ? ' / Ditetapkan '.$kawasan->tanggal_penetapan->translatedFormat('d F Y') : '' }}</p><h2 class="mt-2 font-display text-3xl text-stone-950">{{ $kawasan->nama }}</h2>@if($kawasan->categories->isNotEmpty())<p class="mt-3 text-xs font-bold text-emerald-800">{{ $kawasan->categories->pluck('nama')->join(' / ') }}</p>@endif<p class="mt-3 text-sm leading-6 text-stone-600">{{ str($kawasan->deskripsi ?: 'Profil kawasan sedang dilengkapi pengelola.')->limit(150) }}</p><a href="{{ route('public.kawasan.show', $kawasan) }}" class="back-link mt-5">Lihat detail kawasan</a></article>
                    @endforeach
                </div>
            @endif
            @if ($kawasans->hasPages())<div class="mt-10 border-t border-stone-300 pt-6">{{ $kawasans->links() }}</div>@endif
        </section>
    </div>
@endsection
