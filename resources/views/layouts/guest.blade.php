<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-950 antialiased">
    <a href="#content" class="skip-link">Lewati ke konten</a>
    <main id="content" class="grid min-h-screen lg:grid-cols-[minmax(0,1.08fr)_minmax(28rem,0.92fr)]">
        <section class="relative hidden overflow-hidden bg-emerald-950 px-12 py-14 text-white lg:flex lg:flex-col lg:justify-between xl:px-20">
            <div class="absolute inset-0 opacity-30" aria-hidden="true">
                <div class="absolute -left-28 top-16 size-96 rounded-full border border-emerald-200/40"></div>
                <div class="absolute -left-12 top-32 size-96 rounded-full border border-emerald-200/20"></div>
                <div class="absolute bottom-0 right-0 h-2/3 w-2/3 bg-[radial-gradient(circle_at_bottom_right,rgba(251,191,36,.2),transparent_64%)]"></div>
            </div>
            <div class="relative flex items-center gap-4">
                <div class="grid size-12 place-items-center border border-white/30 bg-white/10 font-display text-xl">MI</div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-200">Kabupaten Madiun</p>
                    <p class="mt-1 font-semibold">Kawasan Perdesaan</p>
                </div>
            </div>
            <div class="relative max-w-2xl">
                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.24em] text-amber-300">Satu data, keputusan lebih baik</p>
                <h1 class="font-display text-5xl leading-[1.04] text-balance xl:text-6xl">Ruang kerja terpadu untuk potensi desa yang bertumbuh.</h1>
                <p class="mt-7 max-w-xl text-lg leading-8 text-emerald-100/80">Kelola data wilayah, kolaborasi antardesa, dan jejak aktivitas secara tertib dalam satu sistem.</p>
            </div>
            <p class="relative text-xs text-emerald-200/70">Dinas Pemberdayaan Masyarakat dan Desa · Kabupaten Madiun</p>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-10 lg:px-14">
            <div class="w-full max-w-md">
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <div class="grid size-11 place-items-center bg-emerald-900 font-display text-lg text-white">MI</div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-800">Kabupaten Madiun</p>
                        <p class="text-sm font-semibold">Kawasan Perdesaan</p>
                    </div>
                </div>
                @include('partials.flash')
                {{ $slot }}
                <p class="mt-10 border-t border-stone-300 pt-5 text-xs leading-5 text-stone-500">Akses terbatas untuk pengelola resmi. Seluruh aktivitas masuk dicatat untuk menjaga akuntabilitas data.</p>
            </div>
        </section>
    </main>
</body>
</html>
