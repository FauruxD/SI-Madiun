<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-950 antialiased">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <div class="min-h-screen lg:grid lg:grid-cols-[17.5rem_minmax(0,1fr)]">
        <div data-sidebar-overlay class="fixed inset-0 z-40 hidden bg-stone-950/55 lg:hidden" aria-hidden="true"></div>
        <aside data-sidebar class="fixed inset-y-0 left-0 z-50 flex w-[17.5rem] -translate-x-full flex-col bg-emerald-950 text-white transition-transform duration-200 ease-out lg:translate-x-0 motion-reduce:transition-none">
            <div class="flex h-20 items-center gap-3 border-b border-white/10 px-6">
                <div class="grid size-10 place-items-center bg-amber-300 font-display text-lg text-emerald-950">MI</div>
                <div class="min-w-0">
                    <p class="truncate text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-200">Kabupaten Madiun</p>
                    <p class="truncate text-sm font-semibold">Kawasan Perdesaan</p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-7" aria-label="Navigasi utama">
                <p class="px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-300/65">Ruang kerja</p>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('dashboard') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('dashboard')]) @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                        <span class="nav-marker"></span><span>Dashboard</span>
                    </a>
                    @if (auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.users.index') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('admin.users.*')]) @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>
                            <span class="nav-marker"></span><span>Pengguna</span>
                        </a>
                        <a href="{{ route('admin.desa.index') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('admin.kabupaten.*', 'admin.kecamatan.*', 'admin.desa.*')]) @if(request()->routeIs('admin.kabupaten.*', 'admin.kecamatan.*', 'admin.desa.*')) aria-current="page" @endif>
                            <span class="nav-marker"></span><span>Master Wilayah</span>
                        </a>
                        <a href="{{ route('admin.kawasan.index') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('admin.kawasan.*', 'admin.kategori-kawasan.*')]) @if(request()->routeIs('admin.kawasan.*', 'admin.kategori-kawasan.*')) aria-current="page" @endif>
                            <span class="nav-marker"></span><span>Kawasan Perdesaan</span>
                        </a>
                    @endif
                    <a href="{{ route('peta.manage') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('peta.*')]) @if(request()->routeIs('peta.*')) aria-current="page" @endif>
                        <span class="nav-marker"></span><span>Peta & Geospasial</span>
                    </a>
                    @can('viewAny', \App\Models\Bumdes::class)
                        <a href="{{ route('bumdes.index') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('bumdes.index', 'bumdes.create', 'bumdes.show', 'bumdes.edit')]) @if(request()->routeIs('bumdes.index', 'bumdes.create', 'bumdes.show', 'bumdes.edit')) aria-current="page" @endif>
                            <span class="nav-marker"></span><span>BUMDes & BUMDesma</span>
                        </a>
                    @endcan
                    @can('viewAny', \App\Models\PotensiDesa::class)
                        <p class="px-3 pb-1 pt-5 text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-300/65">Potensi</p>
                        <a href="{{ route('potensi-desa.index') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('potensi-desa.*', 'admin.kategori-potensi.*')]) @if(request()->routeIs('potensi-desa.*', 'admin.kategori-potensi.*')) aria-current="page" @endif>
                            <span class="nav-marker"></span><span>Potensi Desa</span>
                        </a>
                    @endcan
                    @can('viewAny', \App\Models\EProposal::class)
                        <a href="{{ route('admin.eproposal.index') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('admin.eproposal.*') && ! request()->routeIs('admin.eproposal.review.*')]) @if(request()->routeIs('admin.eproposal.*') && ! request()->routeIs('admin.eproposal.review.*')) aria-current="page" @endif>
                            <span class="nav-marker"></span><span>E-Proposal</span>
                        </a>
                    @endcan
                    @can('reviewAny', \App\Models\EProposal::class)
                        <a href="{{ route('admin.eproposal.review.index') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('admin.eproposal.review.*')]) @if(request()->routeIs('admin.eproposal.review.*')) aria-current="page" @endif><span class="nav-marker"></span><span>Verifikasi Proposal</span></a>
                    @endcan
                    @can('viewAny', \App\Models\RencanaAksi::class)
                        <p class="px-3 pb-1 pt-5 text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-300/65">Kinerja</p>
                        <a href="{{ route('rencana-aksi.index') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('rencana-aksi.*', 'bumdes.rencana-aksi.*')]) @if(request()->routeIs('rencana-aksi.*', 'bumdes.rencana-aksi.*')) aria-current="page" @endif>
                            <span class="nav-marker"></span><span>Rencana & Realisasi</span>
                        </a>
                    @endcan
                    <a href="{{ route('profile.edit') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('profile.*')]) @if(request()->routeIs('profile.*')) aria-current="page" @endif>
                        <span class="nav-marker"></span><span>Profil Saya</span>
                    </a>
                </div>
            </nav>

            <div class="border-t border-white/10 p-4">
                <div class="mb-3 px-3">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="mt-0.5 text-xs text-emerald-200/70">{{ auth()->user()->role->label() }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="w-full border border-white/15 px-3 py-2.5 text-left text-sm font-semibold text-emerald-50 transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">Keluar</button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 lg:col-start-2">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-stone-200 bg-stone-50/95 px-4 backdrop-blur sm:px-7 lg:h-20 lg:px-10">
                <button type="button" data-sidebar-toggle class="grid size-11 place-items-center border border-stone-300 bg-white text-stone-800 lg:hidden" aria-label="Buka navigasi" aria-expanded="false">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <div class="hidden lg:block">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500">Sistem Informasi Kawasan Perdesaan</p>
                    <p class="mt-1 text-sm font-medium text-stone-800">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <a href="{{ route('admin.eproposal.index') }}#proposal-notifications" class="relative grid size-10 place-items-center border border-stone-300 bg-white text-stone-700 transition hover:border-emerald-800 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800" aria-label="Notifikasi E-Proposal, {{ $unreadProposalNotifications }} belum dibaca">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 8h18c0-1-3-1-3-8M10 21h4"/></svg>
                        @if ($unreadProposalNotifications > 0)<span class="absolute -right-1 -top-1 grid min-w-5 place-items-center rounded-full bg-amber-300 px-1 text-[10px] font-bold text-amber-950">{{ $unreadProposalNotifications }}</span>@endif
                    </a>
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-stone-500">{{ auth()->user()->role->shortLabel() }}</p>
                    </div>
                    <div class="grid size-10 place-items-center rounded-full bg-amber-200 text-sm font-bold text-amber-950" aria-hidden="true">{{ str(auth()->user()->name)->explode(' ')->map(fn ($part) => str($part)->substr(0, 1))->take(2)->join('') }}</div>
                </div>
            </header>

            <main id="main-content" class="px-4 py-7 sm:px-7 lg:px-10 lg:py-10">
                <div class="mx-auto max-w-[88rem]">
                    @include('partials.flash')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
