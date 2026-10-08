<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? 'Jelajahi kawasan perdesaan, potensi desa, BUMDes, peta, dan proposal publik Kabupaten Madiun.' }}">
    <title>{{ $title ?? 'Portal Kawasan Perdesaan' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-950 antialiased">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    @php
        $publicNavigation = [
            ['route' => 'public.home', 'label' => 'Beranda', 'active' => 'public.home'],
            ['route' => 'public.kawasan.index', 'label' => 'Kawasan', 'active' => 'public.kawasan.*'],
            ['route' => 'katalog-potensi.index', 'label' => 'Potensi Desa', 'active' => 'katalog-potensi.*'],
            ['route' => 'public.bumdes.index', 'label' => 'BUMDes', 'active' => 'public.bumdes.index'],
            ['route' => 'peta.index', 'label' => 'Peta', 'active' => 'peta.index'],
            ['route' => 'public.about', 'label' => 'Tentang', 'active' => 'public.about'],
        ];
    @endphp
    <header class="border-b border-emerald-900/10 bg-emerald-950 text-white">
        <div class="mx-auto flex min-h-20 max-w-[88rem] items-center justify-between gap-4 px-4 py-3 sm:px-7 lg:px-10">
            <a href="{{ route('public.home') }}" class="flex min-w-0 items-center gap-3 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-300">
                <span class="grid size-10 shrink-0 place-items-center bg-amber-300 font-display text-lg text-emerald-950" aria-hidden="true">MI</span>
                <span class="min-w-0"><span class="block truncate text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-200">Kabupaten Madiun</span><span class="block truncate text-sm font-semibold">Kawasan Perdesaan</span></span>
            </a>
            <nav aria-label="Navigasi publik" class="hidden items-center gap-0.5 lg:flex">
                @foreach ($publicNavigation as $item)
                    <a href="{{ route($item['route']) }}" @class(['inline-flex min-h-11 items-center px-2.5 text-[13px] font-semibold transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300 xl:px-3', 'text-white' => request()->routeIs($item['active']), 'text-emerald-100/75' => ! request()->routeIs($item['active'])]) @if(request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="ml-2 inline-flex min-h-11 items-center border border-white/20 px-3 text-[13px] font-bold transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300 xl:px-4">{{ auth()->check() ? 'Ruang kerja' : 'Masuk admin' }}</a>
            </nav>
            <details class="relative lg:hidden">
                <summary class="flex min-h-11 cursor-pointer list-none items-center border border-white/20 px-4 text-sm font-bold marker:content-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">Menu</summary>
                <nav aria-label="Navigasi publik seluler" class="absolute right-0 top-14 z-50 w-64 border border-emerald-800 bg-emerald-950 p-2 shadow-[0_18px_50px_rgba(2,44,34,0.28)]">
                    @foreach ($publicNavigation as $item)
                        <a href="{{ route($item['route']) }}" @class(['flex min-h-11 items-center px-3 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-amber-300', 'bg-white/10 text-white' => request()->routeIs($item['active']), 'text-emerald-100/80 hover:bg-white/5 hover:text-white' => ! request()->routeIs($item['active'])]) @if(request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @endforeach
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="mt-2 flex min-h-11 items-center border border-white/20 px-3 text-sm font-bold focus-visible:outline-2 focus-visible:outline-amber-300">{{ auth()->check() ? 'Ruang kerja' : 'Masuk admin' }}</a>
                </nav>
            </details>
        </div>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-stone-300 bg-stone-50">
        <div class="mx-auto grid max-w-[88rem] gap-8 px-4 py-10 sm:px-7 lg:grid-cols-[1fr_auto] lg:px-10">
            <div class="max-w-xl"><p class="font-semibold text-emerald-950">Sistem Informasi Kawasan Perdesaan Kabupaten Madiun</p><p class="mt-2 text-sm leading-6 text-stone-600">Data publik ditampilkan sesuai status publikasi dari pengelola resmi. Data draft, dokumen privat, dan informasi kontak internal tidak ditampilkan.</p></div>
            <nav aria-label="Navigasi footer" class="grid grid-cols-2 gap-x-7 gap-y-2 text-sm sm:grid-cols-3">
                @foreach ($publicNavigation as $item)<a href="{{ route($item['route']) }}" class="min-h-8 font-semibold text-stone-600 underline-offset-4 hover:text-emerald-800 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800">{{ $item['label'] }}</a>@endforeach
            </nav>
        </div>
        <div class="border-t border-stone-200"><div class="mx-auto max-w-[88rem] px-4 py-5 text-xs text-stone-500 sm:px-7 lg:px-10">Dinas Pemberdayaan Masyarakat dan Desa, Kabupaten Madiun</div></div>
    </footer>
</body>
</html>
