<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ Str::limit($profile->deskripsi ?: 'Profil, produk, dan layanan '.$profile->nama, 160) }}">
    <title>{{ $profile->nama }} · BUMDes Kabupaten Madiun</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen wrap-break-word bg-stone-100 text-stone-950 antialiased">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <header class="border-b border-stone-300 bg-stone-100">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-7 gap-y-3 px-4 py-5 sm:px-7 lg:px-10">
            <a href="#main-content" class="min-w-0 max-w-full break-words text-sm font-bold text-emerald-950 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700">{{ $profile->nama }}</a>
            <nav class="flex flex-wrap gap-x-6 gap-y-3 text-sm font-medium" aria-label="Navigasi website BUMDes">@foreach(['profil' => 'Profil', 'produk' => 'Produk & layanan', 'organisasi' => 'Organisasi', 'galeri' => 'Galeri'] as $anchor => $label)<a href="#{{ $anchor }}" class="min-h-6 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700">{{ $label }}</a>@endforeach</nav>
        </div>
    </header>
    <main id="main-content" class="mx-auto max-w-7xl px-4 sm:px-7 lg:px-10">
        <section id="profil" class="grid min-w-0 gap-8 border-b border-stone-300 py-10 sm:py-14 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:gap-12">
            <div class="min-w-0"><p class="eyebrow">{{ $profile->jenis->label() }} · Kabupaten Madiun</p><h1 class="mt-5 break-words font-display text-4xl leading-tight text-emerald-950 sm:text-5xl lg:text-6xl">{{ $profile->nama }}</h1><p class="mt-6 whitespace-pre-line text-base leading-7 text-stone-700">{{ $profile->deskripsi ?: 'Profil usaha desa yang mengembangkan produk dan layanan untuk masyarakat.' }}</p><div class="mt-7 flex flex-wrap gap-3"><a href="#produk" class="btn-primary">Jelajahi produk & layanan</a><a href="{{ rtrim(config('app.url'), '/').route('katalog-potensi.index', [], false) }}" class="btn-secondary">Katalog potensi desa</a></div></div>
            <div class="min-w-0">@if($galleryImages->isNotEmpty())<img src="{{ $mediaUrl('gallery', $galleryImages->first()->id) }}" alt="{{ $galleryImages->first()->judul ?: 'Dokumentasi '.$profile->nama }}" width="800" height="600" fetchpriority="high" class="aspect-[4/3] w-full object-cover">@else<div class="flex aspect-[4/3] items-end border border-stone-300 bg-stone-200 p-7"><div><p class="font-display text-3xl text-emerald-950">Ruang usaha desa.</p><p class="mt-3 text-sm text-stone-600">Dokumentasi foto belum tersedia.</p></div></div>@endif</div>
        </section>
        <section class="grid gap-8 border-b border-stone-300 py-8 sm:grid-cols-2 lg:grid-cols-3" aria-label="Identitas BUMDes">
            <div><h2 class="text-sm font-semibold text-stone-500">Desa asal</h2><ul class="mt-3 space-y-2 text-sm">@forelse($profile->desas as $desa)<li>{{ $desa->nama }}, Kecamatan {{ $desa->kecamatan->nama }}{{ $desa->pivot->is_desa_utama ? ' (desa utama)' : '' }}</li>@empty<li>Belum dilengkapi.</li>@endforelse</ul></div>
            <div><h2 class="text-sm font-semibold text-stone-500">Pendirian</h2><p class="mt-3 text-sm">{{ $profile->tahun_berdiri ? 'Berdiri '.$profile->tahun_berdiri : 'Tahun belum dilengkapi' }}</p><p class="mt-2 text-sm text-stone-600">{{ $profile->status_hukum ?: 'Status hukum belum dilengkapi' }}</p></div>
            <div><h2 class="text-sm font-semibold text-stone-500">Alamat usaha</h2><p class="mt-3 whitespace-pre-line text-sm leading-6">{{ $profile->alamat ?: 'Alamat belum dilengkapi.' }}</p></div>
        </section>
        <section id="produk" class="py-10 sm:py-14"><h2 class="font-display text-3xl text-emerald-950 sm:text-4xl">Produk & layanan</h2><p class="mt-3 max-w-xl text-sm leading-6 text-stone-600">Pilihan usaha dari unit aktif {{ $profile->nama }}.</p>
            <div class="mt-8 grid min-w-0 gap-x-7 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">@forelse($products as $product)<article class="min-w-0">@if(in_array($product->id, $productImages, true))<img src="{{ $mediaUrl('product', $product->id) }}" alt="{{ $product->nama }}" width="800" height="600" loading="lazy" class="aspect-[4/3] w-full object-cover">@else<div class="flex aspect-[4/3] items-center justify-center border border-stone-200 bg-stone-200 text-sm text-stone-600">Foto produk belum tersedia</div>@endif<p class="mt-4 text-xs text-stone-500">{{ $product->businessUnit->nama }} · {{ $product->kategori }}</p><h3 class="mt-2 break-words text-lg font-semibold">{{ $product->nama }}</h3><p class="mt-3 whitespace-pre-line text-sm leading-6 text-stone-600">{{ $product->deskripsi ?: 'Informasi produk belum dilengkapi.' }}</p><p class="mt-4 text-sm font-semibold text-emerald-900">{{ $product->harga !== null ? 'Rp '.Number::format($product->harga, locale: 'id').($product->satuan ? ' / '.$product->satuan : '') : 'Harga belum dicantumkan' }}</p></article>@empty<p class="border-l-2 border-emerald-700 pl-5 text-sm leading-6 text-stone-600 sm:col-span-2 lg:col-span-3">Belum ada produk atau layanan aktif yang ditampilkan.</p>@endforelse</div>
            <div class="mt-8">{{ $products->links() }}</div>
        </section>
        <section id="organisasi" class="border-y border-stone-300 py-10 sm:py-14"><h2 class="font-display text-3xl text-emerald-950 sm:text-4xl">Pengelola usaha</h2><div class="mt-7 grid gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">@forelse($profile->managers as $manager)<div class="border-l-2 border-emerald-700 pl-5"><h3 class="break-words text-base font-semibold">{{ $manager->nama }}</h3><p class="mt-2 text-sm text-stone-600">{{ $manager->jabatan }}</p></div>@empty<p class="text-sm text-stone-600">Struktur organisasi belum ditampilkan.</p>@endforelse</div></section>
        <section id="galeri" class="py-10 sm:py-14"><h2 class="font-display text-3xl text-emerald-950 sm:text-4xl">Dokumentasi kegiatan</h2><div class="mt-8 grid gap-6 sm:grid-cols-2">@forelse($galleryImages as $image)<figure class="min-w-0"><img src="{{ $mediaUrl('gallery', $image->id) }}" alt="{{ $image->judul ?: 'Dokumentasi '.$profile->nama }}" width="800" height="600" loading="lazy" class="aspect-[4/3] w-full object-cover"><figcaption class="mt-3 break-words text-sm text-stone-600">{{ $image->judul ?: 'Dokumentasi BUMDes' }}</figcaption></figure>@empty<p class="text-sm text-stone-600">Belum ada dokumentasi foto yang tersedia.</p>@endforelse</div><div class="mt-8">{{ $gallery->links() }}</div></section>
    </main>
    <footer class="border-t border-stone-300"><div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-7 text-xs leading-5 text-stone-600 sm:flex-row sm:justify-between sm:px-7 lg:px-10"><p class="break-words">{{ $profile->nama }} · Kabupaten Madiun</p><p>Data dikelola oleh operator BUMDes/BUMDesma.</p></div></footer>
</body>
</html>
