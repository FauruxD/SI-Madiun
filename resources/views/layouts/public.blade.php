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
    <header class="sticky top-0 z-50 border-b border-stone-200/90 bg-stone-50/95 text-stone-950 shadow-[0_1px_0_rgba(28,25,23,0.04)] backdrop-blur-lg">
        <div class="mx-auto flex min-h-18 max-w-[88rem] items-center justify-between gap-4 px-4 py-2.5 sm:px-7 lg:px-10">
            <a href="{{ route('public.home') }}" class="group flex min-w-0 items-center gap-3 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-800" aria-label="Kawasan Perdesaan Kabupaten Madiun, kembali ke beranda">
                <span class="grid size-10 shrink-0 place-items-center bg-emerald-800 font-display text-lg text-white transition-colors duration-200 group-hover:bg-emerald-700 sm:size-11" aria-hidden="true">MI</span>
                <span class="min-w-0"><span class="block truncate text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-800 sm:text-[11px]">Kabupaten Madiun</span><span class="block truncate text-sm font-semibold text-emerald-950 sm:text-[15px]">Kawasan Perdesaan</span></span>
            </a>
            <nav aria-label="Navigasi publik" class="hidden items-center gap-0.5 lg:flex">
                @foreach ($publicNavigation as $item)
                    <a href="{{ route($item['route']) }}" @class(['relative inline-flex min-h-11 items-center px-2.5 text-[13px] font-semibold transition-colors duration-200 after:absolute after:inset-x-2.5 after:bottom-0 after:h-0.5 after:origin-left after:bg-emerald-800 after:transition-transform after:duration-200 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800 xl:px-3 xl:after:inset-x-3', 'text-emerald-900 after:scale-x-100' => request()->routeIs($item['active']), 'text-stone-600 after:scale-x-0 hover:after:scale-x-100' => ! request()->routeIs($item['active'])]) @if(request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="ml-3 inline-flex min-h-11 items-center bg-emerald-800 px-4 text-[13px] font-bold text-white transition duration-200 hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800 active:translate-y-px motion-reduce:transform-none">{{ auth()->check() ? 'Ruang kerja' : 'Login' }}</a>
            </nav>
            <details data-public-menu class="group relative lg:hidden">
                <summary data-public-menu-summary class="grid size-11 cursor-pointer list-none place-items-center border border-stone-300 bg-white text-emerald-950 transition-colors duration-200 marker:content-none hover:border-emerald-700 hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800" aria-label="Buka menu navigasi">
                    <span class="sr-only">Buka menu navigasi</span>
                    <span class="grid w-5 gap-1.5" aria-hidden="true">
                        <span class="h-0.5 w-full origin-center bg-current transition-transform duration-200 group-open:translate-y-2 group-open:rotate-45 motion-reduce:transition-none"></span>
                        <span class="h-0.5 w-full bg-current transition-opacity duration-200 group-open:opacity-0 motion-reduce:transition-none"></span>
                        <span class="h-0.5 w-full origin-center bg-current transition-transform duration-200 group-open:-translate-y-2 group-open:-rotate-45 motion-reduce:transition-none"></span>
                    </span>
                </summary>
                <nav aria-label="Navigasi publik seluler" class="absolute right-0 top-14 z-50 w-[min(21rem,calc(100vw-2rem))] border border-stone-200 bg-white p-2 shadow-[0_22px_60px_rgba(28,25,23,0.16)]">
                    <p class="px-3 pb-2 pt-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">Navigasi publik</p>
                    @foreach ($publicNavigation as $item)
                        <a href="{{ route($item['route']) }}" @class(['flex min-h-11 items-center border-l-2 px-3 text-sm font-semibold transition-colors duration-150 focus-visible:outline-2 focus-visible:outline-emerald-800', 'border-emerald-700 bg-emerald-50 text-emerald-950' => request()->routeIs($item['active']), 'border-transparent text-stone-600 hover:bg-stone-50 hover:text-emerald-800' => ! request()->routeIs($item['active'])]) @if(request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @endforeach
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="mt-2 flex min-h-11 items-center justify-center bg-emerald-800 px-3 text-sm font-bold text-white transition-colors duration-150 hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800">{{ auth()->check() ? 'Masuk ke ruang kerja' : 'Login pengelola' }}</a>
                </nav>
            </details>
        </div>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="mt-12 bg-emerald-950 text-white sm:mt-16">
        <div class="mx-auto grid max-w-[88rem] gap-10 px-4 py-12 sm:px-7 sm:py-14 lg:grid-cols-[minmax(0,1.25fr)_minmax(22rem,0.75fr)] lg:gap-16 lg:px-10">
            <div class="max-w-2xl">
                <a href="{{ route('public.home') }}" class="inline-flex items-center gap-3 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-300">
                    <span class="grid size-11 place-items-center bg-amber-300 font-display text-lg text-emerald-950" aria-hidden="true">MI</span>
                    <span><span class="block text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-200">Kabupaten Madiun</span><span class="block text-sm font-semibold text-white">Kawasan Perdesaan</span></span>
                </a>
                <p class="mt-5 max-w-xl text-sm leading-6 text-emerald-100/80">Data publik ditampilkan sesuai status publikasi dari pengelola resmi. Data draft, dokumen privat, dan informasi kontak internal tidak ditampilkan.</p>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-200">Jelajahi portal</p>
                <nav aria-label="Navigasi footer" class="mt-4 grid grid-cols-2 gap-x-7 gap-y-1 text-sm sm:grid-cols-3 lg:grid-cols-2">
                    @foreach ($publicNavigation as $item)<a href="{{ route($item['route']) }}" class="inline-flex min-h-10 items-center font-semibold text-emerald-50 underline-offset-4 transition-colors hover:text-amber-300 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">{{ $item['label'] }}</a>@endforeach
                </nav>
            </div>
        </div>
        <div class="border-t border-white/10"><div class="mx-auto max-w-[88rem] px-4 py-5 text-xs text-emerald-100/65 sm:px-7 lg:px-10">Dinas Pemberdayaan Masyarakat dan Desa, Kabupaten Madiun</div></div>
    </footer>
</body>
</html>
